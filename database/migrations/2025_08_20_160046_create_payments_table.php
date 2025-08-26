<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('payments', function (Blueprint $t) {
      $t->id();
      $t->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
      // Polymorphic: Sale | Purchase
      $t->string('paymentable_type');     // 'sales' | 'purchases' (table names)
      $t->unsignedBigInteger('paymentable_id');
      $t->foreignId('method_id')->constrained('payment_methods')->cascadeOnDelete();
      $t->date('date');
      $t->decimal('amount',12,2);
      $t->enum('direction',['in','out']); // sale=in, purchase=out
      $t->string('receipt_no')->nullable(); // via sequences
      $t->string('reference')->nullable();  // optional trx id
      $t->text('note')->nullable();
      $t->timestamps();

      $t->index(['company_id','paymentable_type','paymentable_id']);
      $t->unique(['company_id','receipt_no']); // allow null dup, but unique when set
    });
  }
  public function down(): void { Schema::dropIfExists('payments'); }
};
