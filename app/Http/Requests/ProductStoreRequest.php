<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $cid = $this->user()->company_id;

        return [
            'category_id' => ['nullable','integer','exists:categories,id'],
            'brand_id'    => ['nullable','integer','exists:brands,id'],
            'unit_id'     => ['required','integer','exists:units,id'],
            'tax_rate_id' => ['nullable','integer','exists:tax_rates,id'],

            'sku'   => [
                'required','string','max:100',
                Rule::unique('products','sku')->where(fn($q)=>$q->where('company_id',$cid)),
            ],
            'name'      => ['required','string','max:255'],
            'barcode'   => ['nullable','string','max:64'],

            'cost_price'=> ['required','numeric','min:0'],
            'sell_price'=> ['required','numeric','min:0'],
            'min_stock' => ['required','numeric','min:0'],

            'is_active' => ['sometimes','boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'unit_id.required' => 'Unit নির্বাচন করুন',
        ];
    }
}
