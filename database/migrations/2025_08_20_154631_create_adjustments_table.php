<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('adjustments', function (Blueprint $t) {
      $t->id();
      $t->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
      $t->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
      $t->string('adj_no');
      $t->enum('type',['increase','decrease']);
      $t->date('adj_date')->nullable();
      $t->string('reason')->nullable();
      $t->text('note')->nullable();
      $t->timestamps();
      $t->unique(['company_id','adj_no']);
    });
  }
  public function down(): void { Schema::dropIfExists('adjustments'); }
};
