<?php

namespace App\Traits;

use App\Scopes\TenantScope;

trait BelongsToCompany
{
    protected static function bootBelongsToCompany(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            if (empty($model->company_id)) {
                $companyId = request()->attributes->get('company_id');

                if (!$companyId && auth()->check()) {
                    $companyId = auth()->user()->company_id;
                }
                if ($companyId) {
                    $model->company_id = $companyId;
                }
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }
}
