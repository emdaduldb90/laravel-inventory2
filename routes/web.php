<?php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\Auth\CompanyRegisterController;

// Models (route model binding / closures এ দরকার)
use App\Models\Sale;
use App\Models\Company;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| এই ফাইলে থাকা রাউটগুলো ডিফল্টভাবেই "web" middleware গ্রুপে লোড হয়।
*/

// Home
Route::get('/', function () {
    return view('welcome');
})->name('home');

// ------------------------
// Auth required routes
// ------------------------
Route::middleware(['auth'])->group(function () {

    // Blade + Tailwind Product Form
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products',        [ProductController::class, 'store' ])->name('products.store');

    // Print routes (authenticated)
    Route::get('/print/sales/{sale}', [PrintController::class, 'sale'])
        ->name('print.sale');

    Route::get('/print/purchases/{purchase}', [PrintController::class, 'purchase'])
        ->name('print.purchase');

    // POS receipt (route-model binding)
    Route::get('/print/pos/{sale}', function (Sale $sale) {
        $sale->load(['items.product', 'customer', 'warehouse']);
        $company = Company::find($sale->company_id);
        return view('prints.pos-receipt', compact('sale', 'company'));
    })->name('print.pos');
});

// ------------------------
// Guest only routes
// ------------------------
Route::middleware('guest')->group(function () {
    Route::get('/signup', [CompanyRegisterController::class, 'create'])->name('signup.show');
    Route::post('/signup', [CompanyRegisterController::class, 'store'])->name('signup.store');
});

// ------------------------
// Tenant helpers
// ------------------------

// Separate tenant routes include (যদি থাকে)
require __DIR__ . '/tenant.php';

// Tenant identify টেস্ট
Route::middleware('identify.tenant')->get('/tenant-test', function () {
    $company = app('company');
    return $company ? $company->only(['id', 'name', 'slug']) : ['tenant' => null];
});
