<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToCompany;

/**
 * ----------------------
 * Transfer Model
 * ----------------------
 */
class Transfer extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id','from_warehouse_id','to_warehouse_id',
        'transfer_no','status','transfer_date','note'
    ];

    public function items()
    {
        return $this->hasMany(TransferItem::class);
    }

    public function from()
    {
        return $this->belongsTo(Warehouse::class,'from_warehouse_id');
    }

    public function to()
    {
        return $this->belongsTo(Warehouse::class,'to_warehouse_id');
    }
}
