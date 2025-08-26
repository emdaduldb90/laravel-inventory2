<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToCompany;

/**
 * ----------------------
 * Product Model
 * ----------------------
 */
class Product extends Model
{
    use HasFactory, BelongsToCompany, SoftDeletes;

    protected $fillable = [
        'company_id','sku','name','barcode','category_id','brand_id','unit_id','tax_rate_id',
        'cost_price','sell_price','min_stock','is_active'
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'cost_price' => 'decimal:2',
        'sell_price' => 'decimal:2',
        'min_stock'  => 'decimal:3',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function taxRate()
    {
        return $this->belongsTo(TaxRate::class,'tax_rate_id');
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
