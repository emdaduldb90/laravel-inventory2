<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            'products','warehouses','categories','brands','units','tax-rates',
            'suppliers','customers',
            'purchases','sales','payments',
            'transfers','adjustments','sales-returns','purchase-returns','stock-counts',
            'reports','pos',
        ];

        $perms = [];
        foreach ($groups as $g) {
            foreach (['view','create','update','delete'] as $a) {
                $perms[] = "{$g}.{$a}";
            }
        }
        // extra actions
        $perms = array_merge($perms, [
            'sales.post','purchases.post','transfers.post','adjustments.post',
            'sales-returns.post','purchase-returns.post','stock-counts.apply',
            'reports.view','pos.use',
        ]);

        foreach ($perms as $p) {
            Permission::findOrCreate($p);
        }

        $owner   = Role::findOrCreate('Owner');
        $admin   = Role::findOrCreate('Admin');
        $manager = Role::findOrCreate('Manager');
        $cashier = Role::findOrCreate('Cashier');
        $super   = Role::findOrCreate('SuperAdmin');

        $owner->syncPermissions($perms);
        $super->syncPermissions($perms);

        $admin->syncPermissions(array_filter($perms, function ($p) {
            return !str_starts_with($p, 'transfers.delete') &&
                   !str_starts_with($p, 'adjustments.delete');
        }));

        $manager->syncPermissions([
            'products.view','products.create','products.update',
            'sales.view','sales.create','sales.update','sales.post',
            'purchases.view','purchases.create','purchases.update','purchases.post',
            'customers.view','customers.create','customers.update',
            'suppliers.view','suppliers.create','suppliers.update',
            'reports.view',
        ]);

        $cashier->syncPermissions([
            'sales.view','sales.create','sales.update','sales.post',
            'payments.create','payments.view',
            'pos.use',
        ]);
    }
}
