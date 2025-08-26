<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToCompany;

/**
 * ----------------------
 * Adjustment Model
 * ----------------------
 */
class Adjustment extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id','warehouse_id','adj_no','type','adj_date','reason','note'
    ];

    public function items()
    {
        return $this->hasMany(AdjustmentItem::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
