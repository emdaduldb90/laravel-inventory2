<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Resolve current company_id from request or auth
        $companyId = request()->attributes->get('company_id');

        if (!$companyId && auth()->check()) {
            $companyId = auth()->user()->company_id;
        }

        if ($companyId) {
            $builder->where($model->getTable().'.company_id', $companyId);
        }
    }
}
