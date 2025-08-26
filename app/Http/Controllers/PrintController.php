<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Company;

class PrintController extends Controller
{
    public function sale(Sale $sale)
    {
        // প্রয়োজনীয় রিলেশন প্রিলোড
        $sale->load(['items.product', 'customer', 'warehouse', 'payments']);

        // কোম্পানি resolve (রিলেশন না থাকলে fallback)
        $company = method_exists($sale, 'company') && $sale->company
            ? $sale->company
            : (auth()->user()->company ?? Company::find($sale->company_id));

        // অতিরিক্ত ভ্যারিয়েবল
        $paper = $company->print_paper ?? request('size', 'a4');
        $orientation = $company->print_orientation ?? request('orient', 'portrait');
        $margin = $company->print_margin_mm ?? 12;

        return view('prints.sale', compact('sale', 'company', 'paper', 'orientation', 'margin'));
    }

    public function purchase(Purchase $purchase)
    {
        $purchase->load(['items.product', 'supplier', 'warehouse', 'payments']);

        $company = method_exists($purchase, 'company') && $purchase->company
            ? $purchase->company
            : (auth()->user()->company ?? Company::find($purchase->company_id));

        // অতিরিক্ত ভ্যারিয়েবল
        $paper = $company->print_paper ?? request('size', 'a4');
        $orientation = $company->print_orientation ?? request('orient', 'portrait');
        $margin = $company->print_margin_mm ?? 12;

        return view('prints.purchase', compact('purchase', 'company', 'paper', 'orientation', 'margin'));
    }
}
