<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('sales') && !Schema::hasColumn('sales','returned_total')) {
            Schema::table('sales', function (Blueprint $t) {
                $t->decimal('returned_total', 18, 2)->default(0)->after('total');
            });
        }
        if (Schema::hasTable('purchases') && !Schema::hasColumn('purchases','returned_total')) {
            Schema::table('purchases', function (Blueprint $t) {
                $t->decimal('returned_total', 18, 2)->default(0)->after('total');
            });
        }
    }
    public function down(): void {
        if (Schema::hasTable('sales') && Schema::hasColumn('sales','returned_total')) {
            Schema::table('sales', fn(Blueprint $t) => $t->dropColumn('returned_total'));
        }
        if (Schema::hasTable('purchases') && Schema::hasColumn('purchases','returned_total')) {
            Schema::table('purchases', fn(Blueprint $t) => $t->dropColumn('returned_total'));
        }
    }
};
