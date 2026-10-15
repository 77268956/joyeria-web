<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\InventoryImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\TestCase;

class InventoryImportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var array<int, string>
     */
    protected array $temporaryFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }

        parent::tearDown();
    }

    protected function seller(): User
    {
        return User::where('email', 'vendedor@joyeriaweb.com')->firstOrFail();
    }

    /**
     * @param  array<int, array<int, mixed>>  $rows
     */
    protected function writeSpreadsheet(array $rows, string $extension = 'xlsx'): string
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray($rows, null, 'A1');

        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'joyeria_'.uniqid('', true).'.'.$extension;
        $this->temporaryFiles[] = $path;

        $writer = IOFactory::createWriter($spreadsheet, $extension === 'csv' ? 'Csv' : 'Xlsx');
        $writer->save($path);

        return $path;
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    protected function sampleRows(): array
    {
        return [
            ['SKU', 'Codigo de barras', 'Nombre', 'Categoria', 'Metal', 'Pureza', 'Peso (g)', 'Costo', 'Precio', 'Origen', 'Publico'],
            ['', '', 'Anillo solitario', 'Anillos', 'GOLD', '18K', '5.500', '40.00', '100.00', 'Nueva', 'Si'],
            ['PRD-IMPORT-1', '998877665544', 'Cadena de plata', 'Cadenas y Collares', 'SILVER', '925', '12.250', '20.00', '55.50', 'Nueva', 'No'],
        ];
    }

    public function test_it_imports_rows_from_an_xlsx_file(): void
    {
        $seller = $this->seller();
        $storeId = Store::first()->id;
        $path = $this->writeSpreadsheet($this->sampleRows());

        $result = app(InventoryImportService::class)->import($path, $storeId, $seller->id);

        $this->assertSame(2, $result['imported']);
        $this->assertSame([], $result['errors']);

        $ring = Product::where('name', 'Anillo solitario')->firstOrFail();
        $this->assertSame('5.500', $ring->weight_g);
        $this->assertSame('100.00', $ring->price);
        $this->assertSame('40.00', $ring->cost);
        $this->assertSame(ProductStatus::Available, $ring->status);
        $this->assertSame('GOLD', $ring->metal->code);
        $this->assertSame('18K', $ring->purity->code);
        $this->assertStringStartsWith('PRD-', $ring->sku);
        $this->assertNotSame('', $ring->barcode);

        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $ring->id,
            'type' => 'entry',
            'quantity_delta' => 1,
        ]);

        $chain = Product::where('sku', 'PRD-IMPORT-1')->firstOrFail();
        $this->assertSame('998877665544', $chain->barcode);
        $this->assertFalse($chain->is_public);
        $this->assertSame('SILVER', $chain->metal->code);
    }

    public function test_dry_run_validates_without_persisting(): void
    {
        $seller = $this->seller();
        $storeId = Store::first()->id;
        $path = $this->writeSpreadsheet($this->sampleRows());

        $result = app(InventoryImportService::class)->import($path, $storeId, $seller->id, dryRun: true);

        $this->assertSame(2, $result['imported']);
        $this->assertSame([], $result['errors']);
        $this->assertSame(0, Product::withoutGlobalScopes()->count());
    }

    public function test_rows_with_problems_are_reported_and_do_not_abort_the_import(): void
    {
        $seller = $this->seller();
        $storeId = Store::first()->id;

        $path = $this->writeSpreadsheet([
            ['Nombre', 'Categoria', 'Metal', 'Pureza', 'Peso (g)', 'Precio'],
            ['Anillo ok', 'Anillos', 'GOLD', '18K', '3.000', '80.00'],
            ['Metal raro', 'Anillos', 'PLATINUM', '', '4.000', '90.00'],
            ['Sin precio', 'Anillos', 'GOLD', '18K', '4.000', ''],
        ]);

        $result = app(InventoryImportService::class)->import($path, $storeId, $seller->id);

        $this->assertSame(1, $result['imported']);
        $this->assertCount(2, $result['errors']);
        $this->assertSame(3, $result['errors'][0]['row']);
        $this->assertSame(4, $result['errors'][1]['row']);
        $this->assertDatabaseCount('products', 1);
    }

    public function test_it_imports_from_a_csv_file(): void
    {
        $seller = $this->seller();
        $storeId = Store::first()->id;
        $path = $this->writeSpreadsheet($this->sampleRows(), 'csv');

        $result = app(InventoryImportService::class)->import($path, $storeId, $seller->id);

        $this->assertSame(2, $result['imported']);
        $this->assertSame([], $result['errors']);
    }

    public function test_the_import_command_runs_end_to_end(): void
    {
        $seller = $this->seller();
        $path = $this->writeSpreadsheet($this->sampleRows());

        $this->artisan('inventory:import', [
            'file' => $path,
            '--user' => $seller->email,
        ])->assertSuccessful();

        $this->assertDatabaseCount('products', 2);
    }
}
