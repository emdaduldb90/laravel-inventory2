<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Warehouse;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Sequence;
use App\Services\InventoryService;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PosQuickSale extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'POS (Quick Sale)';
    protected static ?string $navigationGroup = 'POS';
    protected static string $view = 'filament.pages.pos-quick-sale';

    // UI state
    public string $sku = '';
    public int    $qty = 1;

    /** @var array<int,array{product_id:int, name:string, price:float, qty:float, line:float, sku:string}> */
    public array $cart = [];

    public ?int $customer_id  = null;
    public ?int $warehouse_id = null;
    public float $discount = 0;
    public float $tax      = 0;
    public float $paid     = 0;
    public ?int $method_id = null;

    // readonly/calculated
    public float $subtotal = 0;
    public float $total    = 0;
    public float $change   = 0;

    public function mount(): void
    {
        $cid = auth()->user()->company_id;

        // defaults
        $this->customer_id = Customer::where('company_id',$cid)->where('name','Walk-in Customer')->value('id')
            ?? Customer::firstOrCreate(['company_id'=>$cid,'name'=>'Walk-in Customer'])->id;

        $this->warehouse_id = Warehouse::where('company_id',$cid)->orderBy('id')->value('id');

        $this->method_id = PaymentMethod::where('company_id',$cid)->where('type','cash')->orWhere('name','Cash')->orderBy('id')->value('id');

        $this->recalc();
    }

    /* ------------ Cart helpers ------------ */

    public function addBySku(): void
    {
        $this->validate([
            'sku' => 'required|string',
            'qty' => 'required|numeric|min:1',
        ]);

        $cid = auth()->user()->company_id;

        $p = Product::where('company_id',$cid)
            ->where('sku',$this->sku)
            ->first();

        if (!$p) {
            throw ValidationException::withMessages(['sku'=>"SKU '{$this->sku}' not found"]);
        }

        $this->addToCart($p->id, $p->name, (float)($p->sell_price ?? 0), (float)$this->qty, $p->sku);

        // reset
        $this->sku = '';
        $this->qty = 1;
        $this->recalc();
    }

    public function addToCart(int $productId, string $name, float $price, float $qty = 1, string $sku=''): void
    {
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['qty']  += $qty;
            $this->cart[$productId]['line']  = $this->cart[$productId]['qty'] * $this->cart[$productId]['price'];
        } else {
            $this->cart[$productId] = [
                'product_id'=>$productId,
                'name'=>$name,
                'price'=>$price,
                'qty'=>$qty,
                'line'=>$price*$qty,
                'sku'=>$sku,
            ];
        }
        $this->recalc();
    }

    public function inc(int $productId): void
    {
        if (!isset($this->cart[$productId])) return;
        $this->cart[$productId]['qty']++;
        $this->cart[$productId]['line'] = $this->cart[$productId]['qty'] * $this->cart[$productId]['price'];
        $this->recalc();
    }

    public function dec(int $productId): void
    {
        if (!isset($this->cart[$productId])) return;
        $this->cart[$productId]['qty'] = max(1, $this->cart[$productId]['qty'] - 1);
        $this->cart[$productId]['line'] = $this->cart[$productId]['qty'] * $this->cart[$productId]['price'];
        $this->recalc();
    }

    public function remove(int $productId): void
    {
        unset($this->cart[$productId]);
        $this->recalc();
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->discount = 0;
        $this->tax = 0;
        $this->paid = 0;
        $this->recalc();
    }

    public function recalc(): void
    {
        $this->subtotal = collect($this->cart)->sum('line');
        $this->total    = $this->subtotal - (float)$this->discount + (float)$this->tax;
        $this->change   = max(0, (float)$this->paid - $this->total);
    }

    /* ------------ Checkout ------------ */

    public function checkout(): void
    {
        if (empty($this->cart)) {
            throw ValidationException::withMessages(['sku'=>'Cart is empty']);
        }
        $this->validate([
            'customer_id'=>'required|integer',
            'warehouse_id'=>'required|integer',
            'method_id'=>'nullable|integer',
            'paid'=>'nullable|numeric|min:0',
        ]);

        $cid = auth()->user()->company_id;

        DB::transaction(function() use ($cid) {

            $sale = Sale::create([
                'company_id'  => $cid,
                'customer_id' => $this->customer_id,
                'warehouse_id'=> $this->warehouse_id,
                'invoice_no'  => class_exists(Sequence::class)
                    ? Sequence::next('sale',$cid,'POS-')
                    : ('POS-'.now()->format('ymdHis')),
                'status'      => 'draft',
                'issue_date'  => now(),
                'subtotal'    => $this->subtotal,
                'discount'    => $this->discount,
                'tax'         => $this->tax,
                'total'       => $this->total,
            ]);

            foreach ($this->cart as $row) {
                SaleItem::create([
                    'company_id'=>$cid,
                    'sale_id'   =>$sale->id,
                    'product_id'=>$row['product_id'],
                    'quantity'  =>$row['qty'],
                    'unit_price'=>$row['price'],
                    'line_total'=>$row['line'],
                ]);
            }

            // পোস্ট করে স্টক কমানো
            app(InventoryService::class)->postSale($sale->fresh('items'));

            // পেমেন্ট (optional, থাকলে)
            if (class_exists(Payment::class) && $this->paid > 0) {
                Payment::create([
                    'company_id'=>$cid,
                    'sale_id'   =>$sale->id,
                    'method_id' =>$this->method_id,
                    'amount'    =>$this->paid,
                    'paid_at'   => now(),
                    'note'      => 'POS',
                ]);

                // simple due adjust (যদি PaymentService না থাকে)
                $paid = (float)($sale->paid ?? 0) + (float)$this->paid;
                $due  = max(0, (float)$sale->total - $paid - (float)($sale->returned_total ?? 0));
                $sale->update(['paid'=>$paid, 'due'=>$due]);
            }

            // রিসিট লিংক
            session()->flash('pos_last_sale_id', $sale->id);
        });

        $this->clearCart();

        // Blade-এ toast/print লিংক দেখাবো
        $this->dispatch('pos-saved');
    }
}
