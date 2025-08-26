<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToCompany;

/**
 * ----------------------
 * SaleItem Model
 * ----------------------
 */
class SaleItem extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id','sale_id','product_id','quantity',
        'unit_price','tax_amount','discount','line_total'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
