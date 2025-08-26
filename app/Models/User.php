<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    // ✅ কোন কোন ফিল্ড mass-assignable
    protected $fillable = [
        'name', 'email', 'password', 'company_id', 'is_owner',
    ];

    // হাইডেন + কাস্টস
    protected $hidden = ['password','remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_owner' => 'boolean',
        // Laravel 10+: plain text password দিলে নিজেরাই hash করবে
        'password' => 'hashed',
    ];

    public function company()
    { return $this->belongsTo(\App\Models\Company::class); }

}
