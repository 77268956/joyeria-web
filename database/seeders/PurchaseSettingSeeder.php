<?php

namespace Database\Seeders;

use App\Models\Metal;
use App\Models\PurchaseSetting;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Initial purchase-discount defaults per precious metal.
 *
 * The default row (null purity) applies when a piece has no registered purity.
 * See docs/05-esquema-bd.md §7.
 */
class PurchaseSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $store = Store::first();
        $owner = User::where('email', 'admin@joyeriaweb.com')->first();

        if (! $store || ! $owner) {
            return;
        }

        $defaults = [
            'GOLD' => '5.00',
            'SILVER' => '5.00',
        ];

        foreach ($defaults as $code => $discount) {
            $metal = Metal::where('code', $code)->first();

            if (! $metal) {
                continue;
            }

            PurchaseSetting::withoutGlobalScopes()->updateOrCreate(
                [
                    'store_id' => $store->id,
                    'metal_id' => $metal->id,
                    'purity_id' => null,
                ],
                [
                    'discount_percent' => $discount,
                    'updated_by' => $owner->id,
                ],
            );
        }
    }
}
