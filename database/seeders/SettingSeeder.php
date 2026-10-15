<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

/**
 * Default system settings (one row per store).
 *
 * Column defaults already carry the business values; this seeder only ensures
 * a row exists so POS limits and ticket footers are available from day one.
 */
class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Store::query()->each(function (Store $store): void {
            SystemSetting::firstOrCreate(
                ['store_id' => $store->id],
                [
                    'ticket_footer_text' => 'Gracias por su compra. Las piezas tienen garantía por defecto de fabricación.',
                    'default_layaway_terms' => 'Los apartados requieren un abono mínimo del 30%. No se realizan devoluciones en efectivo.',
                    'default_purchase_disclaimer' => 'La valoración de metales se realiza con base en el precio del día y la pureza verificada.',
                ],
            );
        });
    }
}
