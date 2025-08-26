<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('print_paper', 16)->default('a4');        // a4 | letter | a5 | pos80 | pos58
            $table->string('print_orientation', 16)->default('portrait'); // portrait | landscape
            $table->unsignedSmallInteger('print_margin_mm')->default(12); // 0-25
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['print_paper','print_orientation','print_margin_mm']);
        });
    }
};
