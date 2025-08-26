<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DemoSeedCommand extends Command
{
    protected $signature = 'demo:seed';
    protected $description = 'Seed demo data (idempotent)';

    public function handle(): int
    {
        $this->call('db:seed', ['--class' => 'Database\\Seeders\\DemoSeeder']);
        $this->info('Demo data seeded.');
        return self::SUCCESS;
    }
}
