<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Traits\BelongsToCompany;

/**
 * ----------------------
 * Payment Model
 * ----------------------
 */
class Payment extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id','paymentable_type','paymentable_id','method_id',
        'date','amount','direction','receipt_no','reference','note'
    ];

    protected $casts = [
        'date'   => 'date',
        'amount' => 'decimal:2',
    ];

    public function paymentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function method()
    {
        return $this->belongsTo(PaymentMethod::class,'method_id');
    }
}
