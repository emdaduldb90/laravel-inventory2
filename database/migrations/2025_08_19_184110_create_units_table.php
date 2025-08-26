<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();

            // 🔹 Company relation
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();

            // 🔹 Unit fields
            $table->string('name');          // Full name (e.g., Piece, Kilogram)
            $table->string('short_name');    // Short name (e.g., pc, kg)
            $table->tinyInteger('precision')->default(0); // Decimal precision

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
