<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * ----------------------
 * StockCountItem Model
 * ----------------------
 */
class StockCountItem extends Model
{
    protected $fillable = [
        'company_id','stock_count_id','product_id',
        'system_qty','counted_qty','diff_qty','unit_cost','value_diff'
    ];

    public function stockCount()
    {
        return $this->belongsTo(StockCount::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
