<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $store = Store::first() ?? Store::create([
            'name' => 'Joyería Web Principal',
            'code' => 'JOY01',
            'currency' => 'USD',
            'timezone' => 'UTC',
            'is_active' => true,
        ]);

        // 1. Dueño (Super Administrador)
        $owner = User::firstOrCreate(
            ['email' => 'admin@joyeriaweb.com'],
            [
                'store_id' => $store->id,
                'name' => 'Carlos Administrador',
                'password' => Hash::make('admin123'),
                'is_active' => true,
            ]
        );
        $owner->assignRole('Dueño');

        // 2. Gerente
        $manager = User::firstOrCreate(
            ['email' => 'gerente@joyeriaweb.com'],
            [
                'store_id' => $store->id,
                'name' => 'María Gerente',
                'password' => Hash::make('gerente123'),
                'is_active' => true,
            ]
        );
        $manager->assignRole('Gerente');

        // 3. Vendedor
        $seller = User::firstOrCreate(
            ['email' => 'vendedor@joyeriaweb.com'],
            [
                'store_id' => $store->id,
                'name' => 'Juan Vendedor',
                'password' => Hash::make('vendedor123'),
                'is_active' => true,
            ]
        );
        $seller->assignRole('Vendedor');
    }
}
