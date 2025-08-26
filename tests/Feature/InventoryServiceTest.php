<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{Company, Warehouse, Category, Brand, Unit, TaxRate, Supplier, Customer, Product, Stock, Purchase, PurchaseItem, Sale, SaleItem};
use App\Services\InventoryService;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function seedBase(): array
    {
        $company = Company::create(['name'=>'TCo','slug'=>'tco']);
        $wh = Warehouse::create(['company_id'=>$company->id,'name'=>'Main','code'=>'MAIN']);
        $cat = Category::create(['company_id'=>$company->id,'name'=>'Cat','slug'=>'cat']);
        $brand = Brand::create(['company_id'=>$company->id,'name'=>'Br','slug'=>'br']);
        $unit = Unit::create(['company_id'=>$company->id,'short_name'=>'pc','name'=>'Piece','precision'=>0]);
        $tax = TaxRate::create(['company_id'=>$company->id,'name'=>'VAT0','rate'=>0,'inclusive'=>false]);
        $supplier = Supplier::create(['company_id'=>$company->id,'name'=>'Supp']);
        $cust = Customer::create(['company_id'=>$company->id,'name'=>'Cust']);

        $prod = Product::create([
            'company_id'=>$company->id,'sku'=>'SKU1','name'=>'Prod',
            'category_id'=>$cat->id,'brand_id'=>$brand->id,'unit_id'=>$unit->id,
            'tax_rate_id'=>$tax->id,'cost_price'=>100,'sell_price'=>150,'is_active'=>true,
        ]);

        Stock::create(['company_id'=>$company->id,'warehouse_id'=>$wh->id,'product_id'=>$prod->id,'qty_on_hand'=>0,'avg_cost'=>0]);

        return compact('company','wh','supplier','cust','prod');
    }

    public function test_post_purchase_increases_stock_and_avg_cost(): void
    {
        $base = $this->seedBase();
        $po = Purchase::create([
            'company_id'=>$base['company']->id,'supplier_id'=>$base['supplier']->id,'warehouse_id'=>$base['wh']->id,
            'po_number'=>'PO1','status'=>'ordered','order_date'=>now(),'subtotal'=>1000,'total'=>1000,
        ]);
        PurchaseItem::create([
            'company_id'=>$base['company']->id,'purchase_id'=>$po->id,'product_id'=>$base['prod']->id,
            'quantity'=>10,'unit_cost'=>100,'line_total'=>1000,
        ]);

        app(InventoryService::class)->postPurchase($po->fresh('items'));

        $stock = Stock::where('company_id',$base['company']->id)
                      ->where('warehouse_id',$base['wh']->id)
                      ->where('product_id',$base['prod']->id)->first();

        $this->assertEquals(10, (float)$stock->qty_on_hand);
        $this->assertEquals(100, (float)$stock->avg_cost);
    }

    public function test_post_sale_decreases_stock(): void
    {
        $base = $this->seedBase();

        // Seed initial stock via purchase
        $po = Purchase::create([
            'company_id'=>$base['company']->id,'supplier_id'=>$base['supplier']->id,'warehouse_id'=>$base['wh']->id,
            'po_number'=>'PO2','status'=>'ordered','order_date'=>now(),'subtotal'=>1000,'total'=>1000,
        ]);
        PurchaseItem::create([
            'company_id'=>$base['company']->id,'purchase_id'=>$po->id,'product_id'=>$base['prod']->id,
            'quantity'=>10,'unit_cost'=>100,'line_total'=>1000,
        ]);
        app(InventoryService::class)->postPurchase($po->fresh('items'));

        $sale = Sale::create([
            'company_id'=>$base['company']->id,'customer_id'=>$base['cust']->id,'warehouse_id'=>$base['wh']->id,
            'invoice_no'=>'INV1','status'=>'draft','issue_date'=>now(),'subtotal'=>300,'total'=>300,
        ]);
        SaleItem::create([
            'company_id'=>$base['company']->id,'sale_id'=>$sale->id,'product_id'=>$base['prod']->id,
            'quantity'=>3,'unit_price'=>150,'line_total'=>450,
        ]);

        app(InventoryService::class)->postSale($sale->fresh('items'));

        $stock = Stock::where('company_id',$base['company']->id)
                      ->where('warehouse_id',$base['wh']->id)
                      ->where('product_id',$base['prod']->id)->first();

        $this->assertEquals(7, (float)$stock->qty_on_hand);
    }
}
