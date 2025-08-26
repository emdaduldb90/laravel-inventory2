<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToCompany;

/**
 * ----------------------
 * PurchaseItem Model
 * ----------------------
 */
class PurchaseItem extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id','purchase_id','product_id','quantity',
        'unit_cost','tax_amount','discount','line_total'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
}
