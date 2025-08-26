<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'domain', 'timezone', 'is_active',
    ];

    public function users()
    {
        return $this->hasMany(\App\Models\User::class);
    }
}
