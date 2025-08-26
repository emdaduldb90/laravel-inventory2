<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToCompany
{
    protected static function bootBelongsToCompany(): void
    {
        // সব কুয়েরিতে company_id ফিল্টার
        static::addGlobalScope('company', function (Builder $builder) {
            $user = auth()->user();
            if (!$user) return;                         // guest/console
            if (method_exists($user, 'hasRole') && $user->hasRole('SuperAdmin')) return;
            $builder->where($builder->getModel()->getTable().'.company_id', $user->company_id);
        });

        // create করার সময় company_id অটো-সেট
        static::creating(function (Model $model) {
            $user = auth()->user();
            if ($user && empty($model->company_id)) {
                $model->company_id = $user->company_id;
            }
        });
    }

    // দরকার হলে ম্যানুয়ালি কোম্পানি ফিল্টার
    public function scopeForCompany(Builder $q, ?int $companyId = null): Builder
    {
        return $q->withoutGlobalScope('company')
                 ->where($this->getTable().'.company_id', $companyId ?? auth()->user()?->company_id);
    }
}
