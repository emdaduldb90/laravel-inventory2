<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Traits\BelongsToCompany;
use App\Models\Traits\LogsTenantActivity;

/**
 * ----------------------
 * SalesReturn Model
 * ----------------------
 */
class SalesReturn extends Model
{
    protected $fillable = [
        'company_id','sale_id','customer_id','warehouse_id','sr_number',
        'status','return_date','subtotal','discount','tax','total','note',
        'posted_at','posted_by',
    ];

    public function items()
    {
        return $this->hasMany(SalesReturnItem::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
