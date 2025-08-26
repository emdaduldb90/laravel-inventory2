<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCompanyOwnership
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) {
            return $next($request);
        }

        $isSuper = method_exists($user, 'hasRole') && $user->hasRole('SuperAdmin');

        foreach ($request->route()->parameters() as $param) {
            if (is_object($param) && isset($param->company_id) && !$isSuper) {
                if ((int) $param->company_id !== (int) $user->company_id) {

                    // OPTIONAL: activity() helper দিয়ে অডিট লগ
                    activity()
                        ->causedBy($user)
                        ->withProperties([
                            'path'         => $request->path(),
                            'param_class'  => get_class($param),
                            'param_id'     => $param->id ?? null,
                            'their_company'=> $param->company_id,
                            'my_company'   => $user->company_id,
                        ])
                        ->log('blocked cross-company access');

                    abort(403, 'Cross-company access denied.');
                }
            }
        }

        return $next($request);
    }
}
