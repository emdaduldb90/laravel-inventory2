<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToCompany;

/**
 * ----------------------
 * Category Model
 * ----------------------
 */
class Category extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id','parent_id','name','slug'
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class,'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class,'parent_id');
    }
}
