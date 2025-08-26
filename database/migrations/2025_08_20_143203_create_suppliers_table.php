<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('suppliers', function (Blueprint $t) {
      $t->id();
      $t->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
      $t->string('name');
      $t->string('phone')->nullable();
      $t->string('email')->nullable();
      $t->text('address')->nullable();
      $t->timestamps();
      $t->unique(['company_id','name']);
    });
  }
  public function down(): void { Schema::dropIfExists('suppliers'); }
};
