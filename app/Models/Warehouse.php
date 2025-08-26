<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToCompany;

/**
 * ----------------------
 * Warehouse Model
 * ----------------------
 */
class Warehouse extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id','name','code','phone','address','is_default'
    ];

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
