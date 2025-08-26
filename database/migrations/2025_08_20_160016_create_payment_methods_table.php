<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('payment_methods', function (Blueprint $t) {
      $t->id();
      $t->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
      $t->string('name');                              // Cash, Bank, bKash...
      $t->enum('type',['cash','bank','mobile','card','other'])->default('cash');
      $t->json('details')->nullable();                 // account no, etc
      $t->boolean('is_active')->default(true);
      $t->timestamps();
      $t->unique(['company_id','name']);
    });
  }
  public function down(): void { Schema::dropIfExists('payment_methods'); }
};
