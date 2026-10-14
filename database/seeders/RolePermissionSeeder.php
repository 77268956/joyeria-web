<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Define all permissions specified in the TRD & PRD
        $permissions = [
            'sales.create',
            'sales.void',
            'layaways.manage',
            'purchases.create',
            'purchases.approve',
            'inventory.view',
            'inventory.adjust',
            'products.edit_price',
            'metal_price.override',
            'cash.open',
            'cash.close',
            'customers.manage',
            'reports.view',
            'users.manage',
            'settings.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // 2. Roles
        // Role Dueño: has all permissions
        $duenoRole = Role::findOrCreate('Dueño', 'web');
        $duenoRole->syncPermissions(Permission::all());

        // Role Gerente: all except users.manage and settings.manage
        $gerenteRole = Role::findOrCreate('Gerente', 'web');
        $gerenteRole->syncPermissions([
            'sales.create',
            'sales.void',
            'layaways.manage',
            'purchases.create',
            'purchases.approve',
            'inventory.view',
            'inventory.adjust',
            'products.edit_price',
            'metal_price.override',
            'cash.open',
            'cash.close',
            'customers.manage',
            'reports.view',
        ]);

        // Role Vendedor: sales, purchases, stock view, cash open/close, customers
        $vendedorRole = Role::findOrCreate('Vendedor', 'web');
        $vendedorRole->syncPermissions([
            'sales.create',
            'purchases.create',
            'inventory.view',
            'cash.open',
            'cash.close',
            'customers.manage',
        ]);
    }
}
