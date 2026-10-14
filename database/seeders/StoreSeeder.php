<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $store = Store::firstOrCreate(
            ['code' => 'JOY01'],
            [
                'name' => 'Joyería Web Principal',
                'address' => 'Centro Histórico, Av. de los Joyeros #102',
                'phone' => '+1 (555) 019-2834',
                'currency' => 'USD',
                'timezone' => 'America/El_Salvador',
                'is_active' => true,
            ]
        );

        SystemSetting::firstOrCreate(
            ['store_id' => $store->id],
            [
                'system_name' => 'Joyería Web',
                'primary_color' => '#12161F',
                'background_color' => '#F7F4EE',
                'accent_gold_color' => '#B89A5E',
                'accent_light_gold' => '#E3D6B4',
                'purchase_approval_limit' => 500.00,
                'max_discount_percent' => 10.00,
                'layaway_max_days' => 30,
                'ticket_footer_text' => 'Gracias por su preferencia. Joyería Web: Arte y Tradición.',
                'low_stock_alert_threshold' => 5,
                'cash_difference_alert_limit' => 10.00,
                'default_layaway_terms' => 'Los apartados tienen vigencia de 30 días. Abono mínimo inicial del 20%.',
                'default_purchase_disclaimer' => 'La compra de metales está sujeta a comprobación de peso, pureza e identificación legal del vendedor.',
                'metal_api_provider' => 'GoldAPI.io',
                'whatsapp_number' => '+15550192834',
            ]
        );
    }
}
