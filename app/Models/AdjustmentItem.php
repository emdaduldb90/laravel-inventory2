<?php
namespace App\Models;
use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdjustmentItem extends Model {
  use HasFactory, BelongsToCompany;
  protected $fillable = ['company_id','adjustment_id','product_id','quantity','unit_cost'];
  public function adjustment(){ return $this->belongsTo(Adjustment::class); }
  public function product(){ return $this->belongsTo(Product::class); }
}
