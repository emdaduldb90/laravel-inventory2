<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToCompany;

/**
 * ----------------------
 * TaxRate Model
 * ----------------------
 */
class TaxRate extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id','name','rate','inclusive'
    ];

    protected $casts = [
        'rate'      => 'decimal:2',
        'inclusive' => 'boolean'
    ];
}
