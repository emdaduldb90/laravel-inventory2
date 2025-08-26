<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity',15,3);
            $table->decimal('unit_cost',12,4)->default(0);
            $table->dateTime('moved_at');
            $table->enum('type', [
                'opening','purchase','sale','xfer_in','xfer_out',
                'adjust_in','adjust_out','return_in','return_out'
            ]);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            // ✅ custom short index name দেওয়া হলো
            $table->index(
                ['company_id','product_id','warehouse_id','moved_at'], 
                'stock_move_comp_prod_wh_mv_idx'
            );
        });
    }

    public function down(): void {
        Schema::dropIfExists('stock_movements');
    }
};
