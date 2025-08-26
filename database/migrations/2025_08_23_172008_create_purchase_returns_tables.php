<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('purchase_returns', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('company_id')->index();
            $t->unsignedBigInteger('purchase_id')->nullable()->index();
            $t->unsignedBigInteger('supplier_id')->nullable()->index();
            $t->unsignedBigInteger('warehouse_id')->index();
            $t->string('pr_number', 50)->nullable()->index();
            $t->enum('status', ['draft','posted'])->default('draft')->index();
            $t->date('return_date')->nullable();
            $t->decimal('subtotal', 18, 2)->default(0);
            $t->decimal('discount', 18, 2)->default(0);
            $t->decimal('tax', 18, 2)->default(0);
            $t->decimal('total', 18, 2)->default(0);
            $t->text('note')->nullable();
            $t->timestamp('posted_at')->nullable();
            $t->unsignedBigInteger('posted_by')->nullable();
            $t->timestamps();
        });

        Schema::create('purchase_return_items', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('company_id')->index();
            $t->unsignedBigInteger('purchase_return_id')->index();
            $t->unsignedBigInteger('product_id')->index();
            $t->decimal('quantity', 18, 4)->default(0);
            $t->decimal('unit_cost', 18, 4)->nullable();   // valuation; null => use current avg_cost
            $t->decimal('line_total', 18, 2)->default(0);
            $t->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('purchase_return_items');
        Schema::dropIfExists('purchase_returns');
    }
};
