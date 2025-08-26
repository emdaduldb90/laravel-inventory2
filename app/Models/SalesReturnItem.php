<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReturnItem extends Model
{
    protected $fillable = [
        'company_id','sales_return_id','product_id','quantity','unit_price','unit_cost','line_total'
    ];

    public function salesReturn() { return $this->belongsTo(SalesReturn::class); }
    public function product()     { return $this->belongsTo(Product::class); }
}
