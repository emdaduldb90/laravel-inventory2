<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['identify.tenant', 'auth'])
    ->prefix('app')
    ->group(function () {
        Route::get('/dashboard', function () {
            $company = app('company'); // বর্তমান কোম্পানি অবজেক্ট
            return view('tenant.dashboard', ['company' => $company]);
        })->name('tenant.dashboard');
    });
Route::middleware('identify.tenant')->get('/tenant-test', function () {
    $company = app('company');
    return $company ? $company->only(['id','name','slug']) : ['tenant' => null];
});
