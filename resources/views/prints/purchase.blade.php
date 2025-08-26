<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>PO {{ $purchase->po_number }}</title>


    {{-- পেপার সেটিংস --}}
    @include('prints._paper_css', [
        'paper' => $paper ?? 'a4',
        'orientation' => $orientation ?? 'portrait',
        'margin' => $margin ?? 12
    ])

    <style>
        :root{ --fg:#111; --muted:#555; --line:#ddd; }
        *{ box-sizing:border-box; }
        body{ font-family:system-ui,Segoe UI,Roboto,Arial; color:var(--fg); margin:24px; }
        .wrap{ max-width:900px; margin:0 auto; }
        .row{ display:flex; justify-content:space-between; align-items:flex-start; gap:16px; }
        .logo{ height:64px; }
        h1{ margin:0 0 6px; font-size:22px }
        h2{ margin:18px 0 8px; font-size:16px }
        p,td,th{ font-size:13px; line-height:1.5 }
        table{ width:100%; border-collapse:collapse; margin-top:14px }
        th, td{ border:1px solid var(--line); padding:8px }
        th{ background:#fafafa; text-align:left }
        .right{ text-align:right }
        .totals{ width:360px; margin-left:auto; }
        .hr{ height:1px; background:var(--line); margin:14px 0 }
        @media print{
            body{ margin:0 }
            .wrap{ margin:0; padding:16px 24px }
        }
    </style>
</head>
<body onload="window.print()">
<div class="wrap">
    @php
        use Illuminate\Support\Facades\Storage;

        $logo = Storage::disk('public')->exists('company/logo.png') ? asset('storage/company/logo.png') : null;

        $companyName    = $company->name ?? config('app.name');
        $companyAddress = $company->address ?? ($purchase->warehouse->address ?? '');
        $companyPhone   = $company->phone ?? ($purchase->warehouse->phone ?? '');
        $companyEmail   = $company->email ?? '';

        $currency = '৳';

        $items = $purchase->items ?? collect();
        $subtotal = $purchase->subtotal ?? $items->sum('line_total');

        $discount = $purchase->discount ?? 0;
        $tax      = $purchase->tax      ?? 0;

        $total = $purchase->total ?? ($subtotal - (float)$discount + (float)$tax);

        $paid = $purchase->paid ?? ($purchase->payments?->sum('amount') ?? 0);
        $due  = $total - (float)$paid;

        function money_bd($v){ return number_format((float)$v, 2); }
        function dt($d){ return optional($d)->format('Y-m-d'); }
    @endphp

    <div class="row">
        <div>
            @if($logo)
                <img class="logo" src="{{ $logo }}" alt="logo">
            @endif
            <h1>{{ $companyName }}</h1>
            <p>
                {{ $companyAddress }}<br>
                @if($companyPhone) Phone: {{ $companyPhone }} @endif
                @if($companyEmail) • Email: {{ $companyEmail }} @endif
            </p>
        </div>
        <div style="text-align:right">
            <h1>PURCHASE ORDER</h1>
            <p><strong>No:</strong> {{ $purchase->po_number }}</p>
            <p><strong>Date:</strong> {{ dt($purchase->order_date) }}</p>
            <p><strong>Status:</strong> {{ strtoupper($purchase->status ?? 'ordered') }}</p>
        </div>
    </div>

    <div class="row">
        <div>
            <h2>Supplier</h2>
            <p>
                <strong>{{ $purchase->supplier?->name }}</strong><br>
                {!! nl2br(e($purchase->supplier?->address)) !!}<br>
                @if($purchase->supplier?->phone) Phone: {{ $purchase->supplier?->phone }} @endif
                @if($purchase->supplier?->email) • Email: {{ $purchase->supplier?->email }} @endif
            </p>
        </div>
        <div>
            <h2>Deliver To</h2>
            <p>
                <strong>{{ $purchase->warehouse?->code }}</strong><br>
                {!! nl2br(e($purchase->warehouse?->address)) !!}
            </p>
        </div>
    </div>

    <div class="hr"></div>

    <table>
        <thead>
        <tr>
            <th style="width:45%">Product</th>
            <th class="right" style="width:10%">Qty</th>
            <th class="right" style="width:15%">Cost</th>
            <th class="right" style="width:15%">Line</th>
        </tr>
        </thead>
        <tbody>
        @foreach($items as $it)
            <tr>
                <td>{{ $it->product?->name }}</td>
                <td class="right">{{ money_bd($it->quantity) }}</td>
                <td class="right">{{ $currency }} {{ money_bd($it->unit_cost) }}</td>
                <td class="right">{{ $currency }} {{ money_bd($it->line_total) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tbody>
        <tr>
            <td><strong>Subtotal</strong></td>
            <td class="right">{{ $currency }} {{ money_bd($subtotal) }}</td>
        </tr>
        @if($discount && (float)$discount != 0)
            <tr>
                <td>Discount</td>
                <td class="right">- {{ $currency }} {{ money_bd($discount) }}</td>
            </tr>
        @endif
        @if($tax && (float)$tax != 0)
            <tr>
                <td>Tax</td>
                <td class="right">{{ $currency }} {{ money_bd($tax) }}</td>
            </tr>
        @endif
        <tr>
            <td><strong>Total</strong></td>
            <td class="right"><strong>{{ $currency }} {{ money_bd($total) }}</strong></td>
        </tr>
        <tr>
            <td>Paid</td>
            <td class="right">{{ $currency }} {{ money_bd($paid) }}</td>
        </tr>
        <tr>
            <td><strong>Due</strong></td>
            <td class="right"><strong>{{ $currency }} {{ money_bd($due) }}</strong></td>
        </tr>
        </tbody>
    </table>

    @if($purchase->note)
        <p><strong>Note:</strong> {{ $purchase->note }}</p>
    @endif

    <div style="margin-top:24px; color:#555;">
        <small>Generated at {{ now()->format('Y-m-d H:i') }}</small>
    </div>
</div>
</body>
</html>
