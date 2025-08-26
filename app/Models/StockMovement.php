<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToCompany;

/**
 * ----------------------
 * StockMovement Model
 * ----------------------
 */
class StockMovement extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id','warehouse_id','product_id','quantity','unit_cost','moved_at',
        'type','reference_type','reference_id','user_id','note'
    ];

    protected $casts = [
        'quantity'  => 'decimal:3',
        'unit_cost' => 'decimal:4',
        'moved_at'  => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
