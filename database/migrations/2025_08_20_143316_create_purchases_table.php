<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('purchases', function (Blueprint $t) {
      $t->id();
      $t->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
      $t->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
      $t->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
      $t->string('po_number');                       // per-tenant unique
      $t->enum('status',['draft','ordered','received','cancelled'])->default('draft');
      $t->date('order_date')->nullable();
      $t->date('expected_date')->nullable();
      $t->decimal('subtotal',12,2)->default(0);
      $t->decimal('discount',12,2)->default(0);
      $t->decimal('tax',12,2)->default(0);
      $t->decimal('shipping',12,2)->default(0);
      $t->decimal('other',12,2)->default(0);
      $t->decimal('total',12,2)->default(0);
      $t->timestamps();
      $t->unique(['company_id','po_number']);
      $t->index(['company_id','status']);
    });
  }
  public function down(): void { Schema::dropIfExists('purchases'); }
};
