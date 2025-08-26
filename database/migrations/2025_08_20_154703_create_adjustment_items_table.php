<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('adjustment_items', function (Blueprint $t) {
      $t->id();
      $t->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
      $t->foreignId('adjustment_id')->constrained('adjustments')->cascadeOnDelete();
      $t->foreignId('product_id')->constrained('products')->cascadeOnDelete();
      $t->decimal('quantity',12,3);
      $t->decimal('unit_cost',12,4)->nullable(); // increase হলে চাইলে কস্ট দেবেন
      $t->timestamps();
      $t->index(['company_id','adjustment_id']);
    });
  }
  public function down(): void { Schema::dropIfExists('adjustment_items'); }
};
