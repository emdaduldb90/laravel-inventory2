<?php
namespace App\Models;
use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransferItem extends Model {
  use HasFactory, BelongsToCompany;
  protected $fillable = ['company_id','transfer_id','product_id','quantity'];
  public function transfer(){ return $this->belongsTo(Transfer::class); }
  public function product(){ return $this->belongsTo(Product::class); }
}
