<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1) Host নির্ণয়
        $host = $request->getHost();
        $mainHost = parse_url(config('app.url'), PHP_URL_HOST)
                 ?: config('tenancy.main_domain', env('TENANT_MAIN_DOMAIN'));

        $company = null;

        // 2) কাস্টম ডোমেইন ম্যাচ
        if ($host && $company === null) {
            $company = Company::where('domain', $host)->first();
        }

        // 3) সাবডোমেইন → slug ম্যাচ (subdomain.mainHost)
        if ($company === null && $host && $mainHost && str_ends_with($host, $mainHost)) {
            $sub = preg_replace('/\.?'.preg_quote($mainHost, '/').'$/', '', $host);
            if ($sub && !in_array($sub, ['www'])) {
                $company = Company::where('slug', $sub)->first();
            }
        }

        // 4) লোকাল/ডেভে fallback: ?company=slug বা Header: X-Company
        if ($company === null) {
            $slug = $request->query('company') ?: $request->header('X-Company');
            if ($slug) {
                $company = Company::where('slug', $slug)->first();
            }
        }

        // 5) লগইন করা থাকলে ইউজারের company নিন (শেষ fallback)
        if ($company === null && auth()->check() && auth()->user()->company_id) {
            $company = Company::find(auth()->user()->company_id);
        }

        // 6) রিকোয়েস্টে সেট করে দিন (scopes/traits এখান থেকে পড়বে)
        if ($company) {
            $request->attributes->set('company_id', $company->id);
            app()->instance('company', $company);
        }

        return $next($request);
    }
}
