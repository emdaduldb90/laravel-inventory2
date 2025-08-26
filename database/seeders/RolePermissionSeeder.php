<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            'product.view','product.create','product.update','product.delete',
            'warehouse.manage','category.manage','brand.manage',
            'purchase.create','purchase.post','sale.create','sale.post',
            'transfer.post','adjustment.post',
            'payment.receive','payment.pay','report.view',
            'user.manage','settings.manage',
        ];

        foreach ($perms as $p) Permission::findOrCreate($p);

        $owner   = Role::findOrCreate('Owner');
        $manager = Role::findOrCreate('Manager');
        $cashier = Role::findOrCreate('Cashier');
        $staff   = Role::findOrCreate('Staff');

        $owner->syncPermissions($perms);
        $manager->syncPermissions([
            'product.view','product.create','product.update',
            'purchase.create','purchase.post','sale.create','sale.post',
            'transfer.post','adjustment.post','payment.receive','payment.pay','report.view'
        ]);
        $cashier->syncPermissions(['sale.create','sale.post','payment.receive','report.view']);
        $staff->syncPermissions(['product.view']);

        // মালিক ইউজারকে Owner role দিন (ইমেইল প্রয়োজনমতো বদলান)
        $user = \App\Models\User::first(); // বা where('email','owner@ponnobd.com')->first();
        if ($user) $user->syncRoles(['Owner']);
    }
}
