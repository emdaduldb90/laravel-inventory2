<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>POS Receipt</title>
<style>
  @media print { @page { size: 80mm auto; margin: 4mm; } }
  body{font-family: ui-monospace, Menlo, Consolas, monospace; font-size:12px;}
  .center{text-align:center}
  .right{text-align:right}
  .line{border-top:1px dashed #000;margin:6px 0}
  table{width:100%}
</style>
</head>
<body onload="window.print()">
  <div class="center">
    <div style="font-weight:700">{{ $company->name ?? 'Company' }}</div>
    @if(!empty($company->domain)) <div>{{ $company->domain }}</div> @endif
  </div>

  <div class="line"></div>
  <div>
    <div>Invoice: {{ $sale->invoice_no }}</div>
    <div>Date   : {{ \Carbon\Carbon::parse($sale->issue_date)->format('Y-m-d H:i') }}</div>
    <div>Customer: {{ $sale->customer->name ?? 'Walk-in' }}</div>
  </div>
  <div class="line"></div>

  <table>
    <tbody>
    @foreach($sale->items as $it)
      <tr>
        <td>{{ \Illuminate\Support\Str::limit($it->product->name ?? '', 22) }}</td>
        <td class="right">{{ number_format($it->quantity,0) }} x {{ number_format($it->unit_price,2) }}</td>
      </tr>
      <tr>
        <td></td>
        <td class="right"><strong>{{ number_format($it->line_total,2) }}</strong></td>
      </tr>
    @endforeach
    </tbody>
  </table>

  <div class="line"></div>
  <table>
    <tr><td>Subtotal</td><td class="right">{{ number_format($sale->subtotal,2) }}</td></tr>
    <tr><td>Discount</td><td class="right">-{{ number_format($sale->discount,2) }}</td></tr>
    <tr><td>Tax</td><td class="right">{{ number_format($sale->tax,2) }}</td></tr>
    <tr><td><strong>Total</strong></td><td class="right"><strong>{{ number_format($sale->total,2) }}</strong></td></tr>
    <tr><td>Paid</td><td class="right">{{ number_format($sale->paid ?? 0,2) }}</td></tr>
    <tr><td>Change</td><td class="right">{{ number_format(max(0, ($sale->paid ?? 0) - $sale->total),2) }}</td></tr>
    <tr><td>Due</td><td class="right">{{ number_format(max(0, $sale->total - ($sale->paid ?? 0) - ($sale->returned_total ?? 0)),2) }}</td></tr>
  </table>
  <div class="line"></div>

  <div class="center">Thank you!</div>
</body>
</html>
