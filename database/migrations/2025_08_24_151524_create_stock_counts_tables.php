<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('stock_counts')) {
            Schema::create('stock_counts', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('company_id')->index();
                $t->unsignedBigInteger('warehouse_id')->index();
                $t->string('sc_number', 50)->nullable()->index();
                $t->enum('status', ['draft','applied'])->default('draft')->index();
                $t->date('count_date')->nullable();
                $t->decimal('increase_value', 18, 2)->default(0);
                $t->decimal('decrease_value', 18, 2)->default(0);
                $t->text('note')->nullable();
                $t->timestamp('applied_at')->nullable();
                $t->unsignedBigInteger('applied_by')->nullable();
                $t->timestamps();
            });
        }

        if (!Schema::hasTable('stock_count_items')) {
            Schema::create('stock_count_items', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('company_id')->index();
                $t->unsignedBigInteger('stock_count_id')->index();
                $t->unsignedBigInteger('product_id')->index();
                $t->decimal('system_qty', 18, 4)->default(0);
                $t->decimal('counted_qty', 18, 4)->default(0);
                $t->decimal('diff_qty', 18, 4)->default(0); // counted - system
                $t->decimal('unit_cost', 18, 4)->nullable(); // null => use current avg_cost
                $t->decimal('value_diff', 18, 2)->default(0); // diff_qty * unit_cost
                $t->timestamps();
            });
        }
    }

    public function down(): void {
        Schema::dropIfExists('stock_count_items');
        Schema::dropIfExists('stock_counts');
    }
};
