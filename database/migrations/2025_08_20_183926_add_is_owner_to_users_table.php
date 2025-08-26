<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $t) {
            if (!Schema::hasColumn('users','is_owner')) {
                $t->boolean('is_owner')->default(false)->after('company_id');
            }
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $t) {
            if (Schema::hasColumn('users','is_owner')) {
                $t->dropColumn('is_owner');
            }
        });
    }
};
