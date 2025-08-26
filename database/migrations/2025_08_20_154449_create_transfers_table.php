<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('transfers', function (Blueprint $t) {
      $t->id();
      $t->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
      $t->foreignId('from_warehouse_id')->constrained('warehouses')->cascadeOnDelete();
      $t->foreignId('to_warehouse_id')->constrained('warehouses')->cascadeOnDelete();
      $t->string('transfer_no');
      $t->enum('status',['draft','sent','received','cancelled'])->default('draft');
      $t->date('transfer_date')->nullable();
      $t->text('note')->nullable();
      $t->timestamps();
      $t->unique(['company_id','transfer_no']);
    });
  }
  public function down(): void { Schema::dropIfExists('transfers'); }
};
