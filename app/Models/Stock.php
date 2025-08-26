<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToCompany;
use App\Models\Traits\BelongsToCompany as ModelBelongsToCompany; // alias ব্যবহার করা হলো

/**
 * ----------------------
 * Stock Model
 * ----------------------
 */
class Stock extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id','warehouse_id','product_id','qty_on_hand','avg_cost'
    ];

    protected $casts = [
        'qty_on_hand' => 'decimal:3',
        'avg_cost'    => 'decimal:4',
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
