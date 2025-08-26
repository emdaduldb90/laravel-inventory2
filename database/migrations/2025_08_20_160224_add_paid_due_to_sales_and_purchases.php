<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('sales', function (Blueprint $t) {
      if (!Schema::hasColumn('sales','paid')) $t->decimal('paid',12,2)->default(0)->after('total');
      if (!Schema::hasColumn('sales','due'))  $t->decimal('due',12,2)->default(0)->after('paid');
      // ⛔ এখানে কোনো index() কল নেই — আগেই আছে
    });

    Schema::table('purchases', function (Blueprint $t) {
      if (!Schema::hasColumn('purchases','paid')) $t->decimal('paid',12,2)->default(0)->after('total');
      if (!Schema::hasColumn('purchases','due'))  $t->decimal('due',12,2)->default(0)->after('paid');
      // ⛔ এখানে কোনো index() কল নেই — আগেই আছে
    });
  }

  public function down(): void {
    Schema::table('sales', function (Blueprint $t) {
      if (Schema::hasColumn('sales','due'))  $t->dropColumn('due');
      if (Schema::hasColumn('sales','paid')) $t->dropColumn('paid');
    });
    Schema::table('purchases', function (Blueprint $t) {
      if (Schema::hasColumn('purchases','due'))  $t->dropColumn('due');
      if (Schema::hasColumn('purchases','paid')) $t->dropColumn('paid');
    });
  }
};
