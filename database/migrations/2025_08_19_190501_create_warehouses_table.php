<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->unique(['company_id','code']);
        });
    }
    public function down(): void { Schema::dropIfExists('warehouses'); }
};
