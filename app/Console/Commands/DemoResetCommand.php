<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DemoResetCommand extends Command
{
    protected $signature = 'demo:reset {--fresh : migrate:fresh before seeding}';
    protected $description = 'Reset demo: truncate tenant tables and reseed';

    public function handle(): int
    {
        if ($this->option('fresh')) {
            $this->call('migrate:fresh');
        } else {
            // simple truncate of tenant tables (safe list)
            $tables = [
                'warehouses','categories','brands','units','tax_rates','suppliers','customers','products',
                'stocks','stock_movements',
                'purchases','purchase_items','sales','sale_items',
                'payments','payment_methods',
                'sales_returns','sales_return_items','purchase_returns','purchase_return_items',
                'stock_counts','stock_count_items',
            ];
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            foreach ($tables as $t) {
                if (Schema::hasTable($t)) DB::table($t)->truncate();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->call('demo:seed');
        return self::SUCCESS;
    }
}
