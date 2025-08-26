<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Models\{Category, Brand, Unit, TaxRate, Product};
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function create(Request $request)
    {
        $cid = $request->user()->company_id;

        // ড্রপডাউনগুলোর অপশন (id=>name) – শুধু এই কোম্পানির ডেটা
        $categories = Category::where('company_id', $cid)->orderBy('name')->pluck('name', 'id');
        $brands     = Brand::where('company_id', $cid)->orderBy('name')->pluck('name', 'id');
        $units      = Unit::where('company_id', $cid)->orderBy('short_name')->get()
                          ->mapWithKeys(fn($u)=>[$u->id => $u->short_name])->all();
        $taxRates   = TaxRate::where('company_id', $cid)->orderBy('name')->pluck('name', 'id');

        return view('products.create', [
            'companyName' => $request->user()->company->name ?? 'Company',
            'companyId'   => $cid,
            'categories'  => $categories,
            'brands'      => $brands,
            'units'       => $units,
            'taxRates'    => $taxRates,
        ]);
    }

    public function store(ProductStoreRequest $request)
    {
        $user = $request->user();

        // company_id কখনও রিকোয়েস্ট থেকে নেব না — server-side এ সেট
        $data = $request->validated();
        $data['company_id'] = $user->company_id;

        Product::create($data);

        return redirect()->route('products.create')
            ->with('success', 'Product created successfully!');
    }
}
