<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait TenantQuery
{
    public static function getEloquentQuery(): Builder
    {
        $q = parent::getEloquentQuery();
        $user = auth()->user();
        if ($user && (!method_exists($user,'hasRole') || !$user->hasRole('SuperAdmin'))) {
            $q->where($q->getModel()->getTable().'.company_id', $user->company_id);
        }
        return $q;
    }
}
