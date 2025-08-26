<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('qty_on_hand',15,3)->default(0);
            $table->decimal('avg_cost',12,4)->default(0);
            $table->timestamps();
            $table->unique(['company_id','warehouse_id','product_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('stocks'); }
};
