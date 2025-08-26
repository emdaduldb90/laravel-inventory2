<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * ----------------------
 * StockCount Model
 * ----------------------
 */
class StockCount extends Model
{
    protected $fillable = [
        'company_id','warehouse_id','sc_number','status','count_date',
        'increase_value','decrease_value','note','applied_at','applied_by',
    ];

    public function items()
    {
        return $this->hasMany(StockCountItem::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
