<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $sale->invoice_no }}</title>

        {{-- পেপার সেটিংস --}}
        @include('prints._paper_css', [
            'paper' => $paper ?? 'a4',
            'orientation' => $orientation ?? 'portrait',
            'margin' => $margin ?? 12
        ])

    <style>
        :root{ --fg:#111; --muted:#555; --line:#ddd; --accent:#000; }
        *{ box-sizing:border-box; }
        body{ font-family:system-ui,Segoe UI,Roboto,Arial; color:var(--fg); margin:24px; }
        .wrap{ max-width:900px; margin:0 auto; }
        .row{ display:flex; justify-content:space-between; align-items:flex-start; gap:16px; }
        .col{ flex:1 }
        .logo{ height:64px; }
        h1{ margin:0 0 6px; font-size:22px }
        h2{ margin:18px 0 8px; font-size:16px }
        p,li,td,th{ font-size:13px; line-height:1.5 }
        small{ color:var(--muted) }
        table{ width:100%; border-collapse:collapse; margin-top:14px }
        th, td{ border:1px solid var(--line); padding:8px }
        th{ background:#fafafa; text-align:left }
        .right{ text-align:right }
        .totals{ width:360px; margin-left:auto; }
        .badge{ display:inline-block; padding:2px 8px; border:1px solid var(--line); border-radius:12px; font-size:12px }
        .hr{ height:1px; background:var(--line); margin:14px 0 }
        .footer{ margin-top:24px; display:flex; justify-content:space-between; color:var(--muted) }
        @media print{
            body{ margin:0 }
            .wrap{ margin:0; padding:16px 24px }
            .no-print{ display:none !important }
        }
    </style>
</head>
<body onload="window.print()">
<div class="wrap">
    @php
        use Illuminate\Support\Facades\Storage;

        $logo = Storage::disk('public')->exists('company/logo.png') ? asset('storage/company/logo.png') : null;

        $companyName    = $company->name ?? config('app.name');
        $companyAddress = $company->address ?? ($sale->warehouse->address ?? '');
        $companyPhone   = $company->phone ?? ($sale->warehouse->phone ?? '');
        $companyEmail   = $company->email ?? '';

        $currency = '৳';

        $items = $sale->items ?? collect();
        $subtotal = $sale->subtotal ?? $items->sum('line_total');

        // ডিসকাউন্ট/ট্যাক্স থাকলে ধরুন, না থাকলে 0
        $discount = $sale->discount ?? 0;
        $tax      = $sale->tax      ?? 0;

        $total = $sale->total ?? ($subtotal - (float)$discount + (float)$tax);

        // পেমেন্ট/ডিউ
        $paid = $sale->paid ?? ($sale->payments?->sum('amount') ?? 0);
        $due  = $total - (float)$paid;

        function money_bd($v){ return number_format((float)$v, 2); }
        function dt($d){ return optional($d)->format('Y-m-d'); }
    @endphp

    <div class="row">
        <div class="col">
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
            <h1>INVOICE</h1>
            <p><strong>No:</strong> {{ $sale->invoice_no }}</p>
            <p><strong>Date:</strong> {{ dt($sale->issue_date) }}</p>
            <p><span class="badge">{{ strtoupper($sale->status ?? 'draft') }}</span></p>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <h2>Bill To</h2>
            <p>
                <strong>{{ $sale->customer?->name }}</strong><br>
                {!! nl2br(e($sale->customer?->address)) !!}<br>
                @if($sale->customer?->phone) Phone: {{ $sale->customer?->phone }} @endif
                @if($sale->customer?->email) • Email: {{ $sale->customer?->email }} @endif
            </p>
        </div>
        <div class="col">
            <h2>Warehouse</h2>
            <p>
                <strong>{{ $sale->warehouse?->code }}</strong><br>
                {!! nl2br(e($sale->warehouse?->address)) !!}
            </p>
        </div>
    </div>

    <div class="hr"></div>

    <table>
        <thead>
        <tr>
            <th style="width:45%">Product</th>
            <th class="right" style="width:10%">Qty</th>
            <th class="right" style="width:15%">Price</th>
            <th class="right" style="width:15%">Line</th>
        </tr>
        </thead>
        <tbody>
        @foreach($items as $it)
            <tr>
                <td>{{ $it->product?->name }}</td>
                <td class="right">{{ money_bd($it->quantity) }}</td>
                <td class="right">{{ $currency }} {{ money_bd($it->unit_price) }}</td>
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

    @if($sale->note)
        <p><strong>Note:</strong> {{ $sale->note }}</p>
    @endif

    <div class="footer">
        <div>Thank you for your business!</div>
        <div><small>Generated at {{ now()->format('Y-m-d H:i') }}</small></div>
    </div>
</div>
</body>
</html>
