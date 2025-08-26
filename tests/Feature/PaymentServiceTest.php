<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{Company, Warehouse, Customer, Supplier, Sale, Purchase, PaymentMethod};
use App\Services\PaymentService;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function base(): array
    {
        $c = Company::create(['name'=>'TCo','slug'=>'tco']);
        $wh = Warehouse::create(['company_id'=>$c->id,'name'=>'Main','code'=>'MAIN']);
        $cust = Customer::create(['company_id'=>$c->id,'name'=>'Cust']);
        $sup  = Supplier::create(['company_id'=>$c->id,'name'=>'Supp']);
        $cash = PaymentMethod::create(['company_id'=>$c->id,'name'=>'Cash','type'=>'cash']);
        return compact('c','wh','cust','sup','cash');
    }

    public function test_sale_payment_updates_due(): void
    {
        $b = $this->base();
        $sale = Sale::create([
            'company_id'=>$b['c']->id,'customer_id'=>$b['cust']->id,'warehouse_id'=>$b['wh']->id,
            'invoice_no'=>'INV','status'=>'draft','issue_date'=>now(),
            'subtotal'=>1000,'total'=>1000,'paid'=>0,'due'=>1000,
        ]);

        app(PaymentService::class)->receiveSalePayment($sale, 400, $b['cash']->id);

        $sale->refresh();
        $this->assertEquals(400, (float)$sale->paid);
        $this->assertEquals(600, (float)$sale->due);
    }

    public function test_purchase_payment_updates_due(): void
    {
        $b = $this->base();
        $po = Purchase::create([
            'company_id'=>$b['c']->id,'supplier_id'=>$b['sup']->id,'warehouse_id'=>$b['wh']->id,
            'po_number'=>'PO','status'=>'ordered','order_date'=>now(),
            'subtotal'=>2000,'total'=>2000,'paid'=>0,'due'=>2000,
        ]);

        app(PaymentService::class)->paySupplier($po, 500, $b['cash']->id);

        $po->refresh();
        $this->assertEquals(500, (float)$po->paid);
        $this->assertEquals(1500, (float)$po->due);
    }
}
