<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToCompany;

/**
 * ----------------------
 * Brand Model
 * ----------------------
 */
class Brand extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id','name','slug'
    ];
}
