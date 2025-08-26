<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * ----------------------
 * PurchaseReturn Model
 * ----------------------
 */
class PurchaseReturn extends Model
{
    protected $fillable = [
        'company_id','purchase_id','supplier_id','warehouse_id','pr_number',
        'status','return_date','subtotal','discount','tax','total','note',
        'posted_at','posted_by',
    ];

    public function items()
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
