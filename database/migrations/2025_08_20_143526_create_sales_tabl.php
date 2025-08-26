<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('sales', function (Blueprint $t) {
      $t->id();
      $t->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
      $t->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
      $t->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
      $t->string('invoice_no');
      $t->enum('status',['draft','posted','returned','cancelled'])->default('draft');
      $t->date('issue_date')->nullable();
      $t->date('due_date')->nullable();
      $t->decimal('subtotal',12,2)->default(0);
      $t->decimal('discount',12,2)->default(0);
      $t->decimal('tax',12,2)->default(0);
      $t->decimal('shipping',12,2)->default(0);
      $t->decimal('other',12,2)->default(0);
      $t->decimal('total',12,2)->default(0);
      $t->timestamps();
      $t->unique(['company_id','invoice_no']);
      $t->index(['company_id','status']);
    });
  }
  public function down(): void { Schema::dropIfExists('sales'); }
};
