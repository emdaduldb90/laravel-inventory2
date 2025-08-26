<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToCompany;
use App\Models\Traits\BelongsToCompany as ModelBelongsToCompany; // alias করা হলো
use App\Models\Traits\LogsTenantActivity;

/**
 * ----------------------
 * Sale Model
 * ----------------------
 */
class Sale extends Model
{
    use HasFactory, BelongsToCompany, LogsTenantActivity;

    protected $fillable = [
        'company_id','customer_id','warehouse_id','invoice_no','status',
        'issue_date','due_date','subtotal','discount','tax','shipping','other','total'
    ];

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function returns()
    {
        return $this->hasMany(\App\Models\SalesReturn::class);
    }

    public function payments()
    {
        return $this->morphMany(\App\Models\Payment::class, 'payable');
    }
}
