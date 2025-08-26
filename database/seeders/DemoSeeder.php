<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\{Company,Warehouse,Category,Brand,Unit,TaxRate,Supplier,Customer,Product,Stock,Purchase,PurchaseItem,Sale,SaleItem,PaymentMethod,Payment,Sequence};
use App\Services\InventoryService;
use Carbon\Carbon;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Company (if exists, reuse)
        $company = Company::firstOrCreate(
            ['slug' => 'demo'],
            ['name' => 'Demo Company', 'domain' => 'demo.local', 'is_active' => true]
        );

        // Sequences (safe)
        if (class_exists(Sequence::class)) {
            Sequence::firstOrCreate(['company_id'=>$company->id,'key'=>'purchase'],['prefix'=>'PO-']);
            Sequence::firstOrCreate(['company_id'=>$company->id,'key'=>'sale'],['prefix'=>'INV-']);
            Sequence::firstOrCreate(['company_id'=>$company->id,'key'=>'stock_count'],['prefix'=>'SC-']);
        }

        $cid = $company->id;

        // Masters
        $wh = Warehouse::firstOrCreate(['company_id'=>$cid,'code'=>'MAIN'],['name'=>'Main WH']);
        $cat = Category::firstOrCreate(['company_id'=>$cid,'name'=>'Electronics'],['slug'=>'electronics']);
        $brand = Brand::firstOrCreate(['company_id'=>$cid,'name'=>'Pentanik'],['slug'=>'pentanik']);
        $unit = Unit::firstOrCreate(['company_id'=>$cid,'short_name'=>'pc'],['name'=>'Piece','precision'=>0]);
        $tax = TaxRate::firstOrCreate(['company_id'=>$cid,'name'=>'VAT 0%'],['rate'=>0,'inclusive'=>false]);
        $supplier = Supplier::firstOrCreate(['company_id'=>$cid,'name'=>'Default Supplier']);
        $customer = Customer::firstOrCreate(['company_id'=>$cid,'name'=>'Walk-in Customer']);

        $cash = PaymentMethod::firstOrCreate(['company_id'=>$cid,'name'=>'Cash'],['type'=>'cash']);

        // Products
        $tv = Product::firstOrCreate([
            'company_id'=>$cid,'sku'=>'TV-43-GP-001'
        ],[
            'name'=>"Pentanik 43\" Google TV",
            'category_id'=>$cat->id,'brand_id'=>$brand->id,'unit_id'=>$unit->id,
            'tax_rate_id'=>$tax->id,'cost_price'=>30000,'sell_price'=>33900,'min_stock'=>2,'is_active'=>true,
        ]);

        // Start clean stock row
        Stock::firstOrCreate(['company_id'=>$cid,'warehouse_id'=>$wh->id,'product_id'=>$tv->id],['qty_on_hand'=>0,'avg_cost'=>0]);

        // 1) Purchase (5 pcs) -> post
        $po = Purchase::firstOrCreate([
            'company_id'=>$cid,'supplier_id'=>$supplier->id,'warehouse_id'=>$wh->id,'po_number'=>'PO-DEMO-1'
        ],[
            'status'=>'ordered','order_date'=>Carbon::now(),'subtotal'=>150000,'total'=>150000,
        ]);

        PurchaseItem::firstOrCreate([
            'company_id'=>$cid,'purchase_id'=>$po->id,'product_id'=>$tv->id
        ],[
            'quantity'=>5,'unit_cost'=>30000,'line_total'=>150000,
        ]);

        app(InventoryService::class)->postPurchase($po->fresh('items'));

        // 2) Sale (2 pcs) -> post
        $sale = Sale::firstOrCreate([
            'company_id'=>$cid,'customer_id'=>$customer->id,'warehouse_id'=>$wh->id,'invoice_no'=>'INV-DEMO-1'
        ],[
            'status'=>'draft','issue_date'=>Carbon::now(),'subtotal'=>67800,'total'=>67800,
        ]);

        SaleItem::firstOrCreate([
            'company_id'=>$cid,'sale_id'=>$sale->id,'product_id'=>$tv->id
        ],[
            'quantity'=>2,'unit_price'=>33900,'line_total'=>67800,
        ]);

        app(InventoryService::class)->postSale($sale->fresh('items'));

        // 3) Partial payment
        Payment::firstOrCreate([
            'company_id'=>$cid,'sale_id'=>$sale->id,'method_id'=>$cash->id,'amount'=>50000
        ],[
            'paid_at'=>now(),'note'=>'Demo seed',
        ]);

        $sale->update(['paid'=>50000,'due'=>max(0,$sale->total-50000-($sale->returned_total??0))]);
    }
}
