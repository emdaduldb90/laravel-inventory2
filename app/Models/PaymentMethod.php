<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToCompany;

/**
 * ----------------------
 * PaymentMethod Model
 * ----------------------
 */
class PaymentMethod extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id','name','type','details','is_active'
    ];

    protected $casts = [
        'details'   => 'array',
        'is_active' => 'boolean',
    ];
}
