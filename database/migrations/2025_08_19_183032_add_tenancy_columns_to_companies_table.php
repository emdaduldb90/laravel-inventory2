<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // আগে name আছে বলেই ধরে নিচ্ছি; এখন slug সহ বাকি ফিল্ড যোগ করছি
            $table->string('slug')->unique()->after('name');
            $table->string('domain')->unique()->nullable()->after('slug');
            $table->string('timezone')->default('Asia/Dhaka')->after('domain');
            $table->boolean('is_active')->default(true)->after('timezone');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'timezone', 'domain', 'slug']);
        });
    }
};
