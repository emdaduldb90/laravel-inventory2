<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // যদি আগে থেকে থেকে থাকে, প্রথমে সেফলি হ্যান্ডেল
            if (!Schema::hasColumn('users', 'company_id')) {
                // BIGINT UNSIGNED + NULLABLE
                $table->foreignId('company_id')
                      ->nullable()
                      ->constrained('companies') // references id on companies
                      ->nullOnDelete();           // ON DELETE SET NULL
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // foreign key নাম সাধারণত users_company_id_foreign
            if (Schema::hasColumn('users', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            }
        });
    }
};
