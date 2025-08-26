<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('purchase_items', function (Blueprint $t) {
      $t->id();
      $t->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
      $t->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
      $t->foreignId('product_id')->constrained('products')->cascadeOnDelete();
      $t->decimal('quantity',12,3);
      $t->decimal('unit_cost',12,4);
      $t->decimal('tax_amount',12,2)->default(0);
      $t->decimal('discount',12,2)->default(0);
      $t->decimal('line_total',12,2);
      $t->timestamps();
      $t->index(['company_id','purchase_id']);
    });
  }
  public function down(): void { Schema::dropIfExists('purchase_items'); }
};
