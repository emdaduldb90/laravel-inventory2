<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('sequences', function (Blueprint $t) {
      $t->id();
      $t->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
      $t->string('key');     // 'purchase','sale'
      $t->string('prefix')->default('');
      $t->unsignedBigInteger('next_number')->default(1);
      $t->unsignedTinyInteger('padding')->default(5);
      $t->timestamps();
      $t->unique(['company_id','key']);
    });
  }
  public function down(): void { Schema::dropIfExists('sequences'); }
};
