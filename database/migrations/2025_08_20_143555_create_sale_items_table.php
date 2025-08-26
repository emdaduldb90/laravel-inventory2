<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('sale_items', function (Blueprint $t) {
      $t->id();
      $t->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
      $t->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
      $t->foreignId('product_id')->constrained('products')->cascadeOnDelete();
      $t->decimal('quantity',12,3);
      $t->decimal('unit_price',12,4);
      $t->decimal('tax_amount',12,2)->default(0);
      $t->decimal('discount',12,2)->default(0);
      $t->decimal('line_total',12,2);
      $t->timestamps();
      $t->index(['company_id','sale_id']);
    });
  }
  public function down(): void { Schema::dropIfExists('sale_items'); }
};
