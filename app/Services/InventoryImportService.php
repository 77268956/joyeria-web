<?php

namespace App\Services;

use App\Enums\ProductOrigin;
use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Location;
use App\Models\Metal;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;
use Throwable;

/**
 * Imports the initial inventory from an Excel (.xlsx) or CSV file.
 *
 * Every created piece is registered through {@see InventoryService}, so the
 * immutable movement ledger remains the single source of truth for stock.
 * The whole import runs inside one transaction (rolled back for --dry-run).
 */
class InventoryImportService
{
    /**
     * Canonical fields mapped to their accepted header aliases (normalised).
     *
     * @var array<string, array<int, string>>
     */
    protected const HEADER_ALIASES = [
        'sku' => ['sku', 'codigo', 'codigo interno', 'clave', 'referencia'],
        'barcode' => ['barcode', 'codigo de barras', 'codigo barras', 'codigo de barra', 'ean'],
        'name' => ['name', 'nombre', 'pieza', 'producto', 'articulo', 'descripcion corta'],
        'description' => ['description', 'descripcion', 'detalle', 'observaciones', 'notas'],
        'category' => ['category', 'categoria', 'tipo', 'linea'],
        'metal' => ['metal', 'material', 'aleacion'],
        'purity' => ['purity', 'pureza', 'ley', 'kilate', 'quilate', 'kilataje'],
        'weight_g' => ['weight_g', 'peso', 'peso g', 'peso gramos', 'gramos', 'weight', 'peso gr'],
        'cost' => ['cost', 'costo', 'coste', 'precio compra', 'precio de compra'],
        'price' => ['price', 'precio', 'precio venta', 'precio de venta', 'pvp'],
        'status' => ['status', 'estado', 'situacion'],
        'origin' => ['origin', 'origen', 'procedencia'],
        'location' => ['location', 'ubicacion', 'lugar', 'vitrina'],
        'is_public' => ['is_public', 'publico', 'publicado', 'visible', 'web', 'mostrar en web'],
    ];

    /**
     * Cached catalog lookups shared across all rows of a single import.
     *
     * @var array<string, mixed>
     */
    protected array $lookups = [];

    public function __construct(protected InventoryService $inventory) {}

    /**
     * Run the import.
     *
     * @return array{imported: int, skipped: int, errors: array<int, array{row: int, message: string}>}
     */
    public function import(string $path, int $storeId, int $userId, bool $dryRun = false): array
    {
        $rows = $this->readRows($path);

        if ($rows === []) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => [['row' => 0, 'message' => 'El archivo no contiene filas de datos.']]];
        }

        $this->buildLookups($storeId);

        $result = ['imported' => 0, 'skipped' => 0, 'errors' => []];

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +1 header row, +1 for 1-based human numbering

                if ($this->rowIsEmpty($row)) {
                    $result['skipped']++;

                    continue;
                }

                try {
                    DB::transaction(fn () => $this->importRow($row, $storeId, $userId));
                    $result['imported']++;
                } catch (Throwable $exception) {
                    $result['errors'][] = ['row' => $rowNumber, 'message' => $exception->getMessage()];
                }
            }

            $dryRun ? DB::rollBack() : DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }

        return $result;
    }

    /**
     * Parse the spreadsheet into associative rows keyed by canonical field.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function readRows(string $path): array
    {
        if (! is_file($path)) {
            throw new RuntimeException("No se encontró el archivo: {$path}");
        }

        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);

        $grid = $reader->load($path)->getActiveSheet()->toArray(null, true, false, false);

        if ($grid === []) {
            return [];
        }

        $aliasMap = $this->aliasMap();
        $header = array_shift($grid);

        $columns = [];
        foreach ($header as $columnIndex => $label) {
            $normalized = $this->normalize((string) $label);

            if ($normalized !== '' && isset($aliasMap[$normalized])) {
                $columns[$columnIndex] = $aliasMap[$normalized];
            }
        }

        if (! in_array('name', $columns, true)) {
            throw new RuntimeException('La hoja no tiene una columna "Nombre" reconocible.');
        }

        $rows = [];

        foreach ($grid as $line) {
            $mapped = [];
            foreach ($columns as $columnIndex => $field) {
                $mapped[$field] = $line[$columnIndex] ?? null;
            }

            if ($mapped !== []) {
                $rows[] = $mapped;
            }
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function importRow(array $row, int $storeId, int $userId): void
    {
        $name = trim((string) ($row['name'] ?? ''));

        if ($name === '') {
            throw new RuntimeException('Falta el nombre de la pieza.');
        }

        $weight = $this->toDecimal($row['weight_g'] ?? null, 3);
        if ($weight === null || bccomp($weight, '0', 3) <= 0) {
            throw new RuntimeException('El peso (g) es requerido y debe ser mayor que cero.');
        }

        $price = $this->toDecimal($row['price'] ?? null, 2);
        if ($price === null) {
            throw new RuntimeException('El precio es requerido.');
        }

        $cost = $this->toDecimal($row['cost'] ?? null, 2) ?? '0.00';

        $categoryId = $this->resolveCategory($row['category'] ?? null);
        $metalId = $this->resolveMetal($row['metal'] ?? null);
        $purityId = $this->resolvePurity($row['purity'] ?? null, $metalId);
        $locationId = $this->resolveLocation($row['location'] ?? null, $storeId);

        $product = Product::create([
            'store_id' => $storeId,
            'sku' => $this->resolveSku($row['sku'] ?? null, $storeId),
            'barcode' => $this->resolveBarcode($row['barcode'] ?? null, $storeId),
            'name' => $name,
            'description' => $this->nullify($row['description'] ?? null),
            'category_id' => $categoryId,
            'metal_id' => $metalId,
            'purity_id' => $purityId,
            'weight_g' => $weight,
            'cost' => $cost,
            'price' => $price,
            'location_id' => $locationId,
            'status' => $this->resolveStatus($row['status'] ?? null),
            'origin' => $this->resolveOrigin($row['origin'] ?? null),
            'is_featured' => false,
            'is_public' => $this->toBool($row['is_public'] ?? null),
        ]);

        $this->inventory->registerProductEntry($product, null, 'Importación inicial de inventario', $userId);
    }

    protected function resolveCategory(mixed $value): int
    {
        $key = $this->normalize((string) $value);

        if ($key === '') {
            throw new RuntimeException('La categoría es requerida.');
        }

        if (isset($this->lookups['categories'][$key])) {
            return $this->lookups['categories'][$key];
        }

        $category = Category::create([
            'name' => trim((string) $value),
            'slug' => Str::slug((string) $value),
            'is_active' => true,
        ]);

        $this->lookups['categories'][$key] = $category->id;

        return $category->id;
    }

    protected function resolveMetal(mixed $value): int
    {
        $key = $this->normalize((string) $value);

        if ($key === '') {
            throw new RuntimeException('El metal es requerido.');
        }

        if (! isset($this->lookups['metals'][$key])) {
            throw new RuntimeException("Metal no reconocido: «{$value}». Regístralo antes de importar.");
        }

        return $this->lookups['metals'][$key];
    }

    protected function resolvePurity(mixed $value, int $metalId): ?int
    {
        $key = $this->normalize((string) $value);

        if ($key === '') {
            return null;
        }

        if (isset($this->lookups['purities'][$metalId][$key])) {
            return $this->lookups['purities'][$metalId][$key];
        }

        throw new RuntimeException("Pureza no reconocida para el metal seleccionado: «{$value}».");
    }

    protected function resolveLocation(mixed $value, int $storeId): ?int
    {
        $key = $this->normalize((string) $value);

        if ($key === '') {
            return null;
        }

        if (isset($this->lookups['locations'][$key])) {
            return $this->lookups['locations'][$key];
        }

        $location = Location::create([
            'store_id' => $storeId,
            'name' => trim((string) $value),
            'type' => 'bodega',
            'is_active' => true,
        ]);

        $this->lookups['locations'][$key] = $location->id;

        return $location->id;
    }

    protected function resolveSku(mixed $value, int $storeId): string
    {
        $sku = trim((string) ($value ?? ''));

        if ($sku === '') {
            return Product::generateSku($storeId);
        }

        $exists = Product::withoutGlobalScopes()
            ->where('store_id', $storeId)
            ->where('sku', $sku)
            ->exists();

        if ($exists) {
            throw new RuntimeException("El SKU «{$sku}» ya existe en esta tienda.");
        }

        return $sku;
    }

    protected function resolveBarcode(mixed $value, int $storeId): string
    {
        $barcode = trim((string) ($value ?? ''));

        if ($barcode === '') {
            return Product::generateBarcode($storeId);
        }

        $exists = Product::withoutGlobalScopes()
            ->where('store_id', $storeId)
            ->where('barcode', $barcode)
            ->exists();

        if ($exists) {
            throw new RuntimeException("El código de barras «{$barcode}» ya existe en esta tienda.");
        }

        return $barcode;
    }

    protected function resolveStatus(mixed $value): ProductStatus
    {
        return $this->lookups['statuses'][$this->normalize((string) $value)] ?? ProductStatus::Available;
    }

    protected function resolveOrigin(mixed $value): ProductOrigin
    {
        return $this->lookups['origins'][$this->normalize((string) $value)] ?? ProductOrigin::New;
    }

    /**
     * Pre-load catalog lookups to avoid per-row queries.
     */
    protected function buildLookups(int $storeId): void
    {
        $categories = [];
        foreach (Category::query()->get() as $category) {
            $categories[$this->normalize($category->name)] = $category->id;
            $categories[$this->normalize($category->slug)] = $category->id;
        }

        $metals = [];
        $purities = [];
        foreach (Metal::query()->with('purities')->get() as $metal) {
            $metals[$this->normalize($metal->code)] = $metal->id;
            $metals[$this->normalize($metal->name)] = $metal->id;

            foreach ($metal->purities as $purity) {
                $purities[$metal->id][$this->normalize($purity->code)] = $purity->id;
                $purities[$metal->id][$this->normalize($purity->label)] = $purity->id;
            }
        }

        $locations = [];
        foreach (Location::query()->where('store_id', $storeId)->get() as $location) {
            $locations[$this->normalize($location->name)] = $location->id;
        }

        $statuses = [];
        foreach (ProductStatus::cases() as $status) {
            $statuses[$this->normalize($status->value)] = $status;
            $statuses[$this->normalize($status->label())] = $status;
        }

        $origins = [];
        foreach (ProductOrigin::cases() as $origin) {
            $origins[$this->normalize($origin->value)] = $origin;
            $origins[$this->normalize($origin->label())] = $origin;
        }

        $this->lookups = [
            'categories' => $categories,
            'metals' => $metals,
            'purities' => $purities,
            'locations' => $locations,
            'statuses' => $statuses,
            'origins' => $origins,
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function aliasMap(): array
    {
        $map = [];

        foreach (self::HEADER_ALIASES as $field => $aliases) {
            foreach ($aliases as $alias) {
                $map[$this->normalize($alias)] = $field;
            }
        }

        return $map;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Lower-case, accent-stripped header key.
     */
    protected function normalize(string $value): string
    {
        $value = preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;
        $value = Str::ascii(mb_strtolower(trim($value)));
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?? '';

        return trim($value);
    }

    protected function toDecimal(mixed $value, int $scale): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return number_format((float) $value, $scale, '.', '');
        }

        $number = preg_replace('/[^0-9,.\-]/', '', (string) $value) ?? '';

        if (str_contains($number, ',') && str_contains($number, '.')) {
            $number = str_replace(',', '', $number);
        } elseif (str_contains($number, ',')) {
            $number = str_replace(',', '.', $number);
        }

        if (! is_numeric($number)) {
            return null;
        }

        return number_format((float) $number, $scale, '.', '');
    }

    protected function toBool(mixed $value): bool
    {
        if ($value === null || trim((string) $value) === '') {
            return true;
        }

        return in_array($this->normalize((string) $value), ['1', 'si', 'yes', 'y', 'true', 'verdadero', 'publico', 'x'], true);
    }

    protected function nullify(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}
