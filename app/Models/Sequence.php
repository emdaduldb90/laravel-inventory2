<?php
namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Sequence extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id','key','prefix','next_number','padding'];

    public static function next(string $key, int $companyId, string $prefix=''): string
    {
        $seq = static::firstOrCreate(
            ['company_id'=>$companyId,'key'=>$key],
            ['prefix'=>$prefix,'next_number'=>1,'padding'=>5]
        );
        $number = $seq->prefix . str_pad($seq->next_number, $seq->padding, '0', STR_PAD_LEFT);
        $seq->increment('next_number');
        return $number;
    }
}
