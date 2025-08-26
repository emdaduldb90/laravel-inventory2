<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToCompany;
use App\Models\Traits\BelongsToCompany as ModelBelongsToCompany;

/**
 * ----------------------
 * Purchase Model
 * ----------------------
 */
class Purchase extends Model
{
    use HasFactory, BelongsToCompany;
    use ModelBelongsToCompany;

    protected $fillable = [
        'company_id','supplier_id','warehouse_id','po_number','status',
        'order_date','expected_date','subtotal','discount','tax','shipping','other','total'
    ];

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function returns()
    {
        return $this->hasMany(\App\Models\PurchaseReturn::class);
    }

    public function payments()
    {
        return $this->morphMany(\App\Models\Payment::class, 'payable');
    }
}
