<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('transfer_items', function (Blueprint $t) {
      $t->id();
      $t->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
      $t->foreignId('transfer_id')->constrained('transfers')->cascadeOnDelete();
      $t->foreignId('product_id')->constrained('products')->cascadeOnDelete();
      $t->decimal('quantity',12,3);
      $t->timestamps();
      $t->index(['company_id','transfer_id']);
    });
  }
  public function down(): void { Schema::dropIfExists('transfer_items'); }
};
