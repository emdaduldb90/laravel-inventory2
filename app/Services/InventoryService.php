<?php
namespace App\Services;

use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\Adjustment;
use App\Models\AdjustmentItem;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Spatie\Activitylog\Models\Activity;

class InventoryService
{
    /**
     * Adjust stock & movements. $qty positive => in, negative => out.
     * Moving-average cost recalculated on IN only.
     */
    public function adjust(
        int $companyId, int $warehouseId, int $productId,
        float $qty, float $unitCost, string $type,
        ?string $refType=null, ?int $refId=null, ?int $userId=null, ?string $note=null
    ): void {
        DB::transaction(function () use ($companyId,$warehouseId,$productId,$qty,$unitCost,$type,$refType,$refId,$userId,$note) {
            $stock = Stock::firstOrCreate(
                ['company_id'=>$companyId,'warehouse_id'=>$warehouseId,'product_id'=>$productId],
                ['qty_on_hand'=>0,'avg_cost'=>0]
            );

            if ($qty < 0 && $stock->qty_on_hand + $qty < 0) {
                throw new InvalidArgumentException('Insufficient stock for product '.$productId);
            }

            if ($qty > 0) {
                $oldQty = (float)$stock->qty_on_hand;
                $oldAvg = (float)$stock->avg_cost;
                $newQty = $oldQty + $qty;
                $newAvg = $newQty > 0
                    ? (($oldQty * $oldAvg) + ($qty * $unitCost)) / $newQty
                    : $unitCost;
                $stock->avg_cost = $newAvg;
            }

            $stock->qty_on_hand = $stock->qty_on_hand + $qty;
            $stock->save();

            StockMovement::create([
                'company_id'=>$companyId,
                'warehouse_id'=>$warehouseId,
                'product_id'=>$productId,
                'quantity'=>$qty,
                'unit_cost'=>$unitCost,
                'moved_at'=>now(),
                'type'=>$type,
                'reference_type'=>$refType,
                'reference_id'=>$refId,
                'user_id'=>$userId,
                'note'=>$note,
            ]);
        });
    }

    public function postPurchase(Purchase $purchase): void
    {
        if ($purchase->status === 'received') return;

        foreach ($purchase->items as $it) {
            $this->adjust(
                $purchase->company_id, $purchase->warehouse_id, $it->product_id,
                + (float)$it->quantity, (float)$it->unit_cost,
                'purchase','purchases',$purchase->id
            );
        }
        $purchase->status = 'received';
        $purchase->save();

        // 🔹 Activity log
        activity()
            ->performedOn($purchase)
            ->causedBy(auth()->user())
            ->withProperties(['purchase_id' => $purchase->id])
            ->log('purchase posted');
    }

    public function postSale(Sale $sale): void
    {
        if ($sale->status === 'posted') return;

        foreach ($sale->items as $it) {
            $cid = $sale->company_id;
            $wid = $sale->warehouse_id;
            $pid = $it->product_id;
            $qty = (float)$it->quantity;

            if (!config('inventory.allow_negative_stock')) {
                $current = Stock::where('company_id',$cid)
                    ->where('warehouse_id',$wid)
                    ->where('product_id',$pid)
                    ->value('qty_on_hand') ?? 0;

                if ($current < $qty) {
                    throw new \RuntimeException("Insufficient stock for product ID {$pid} in WH {$wid}");
                }
            }

            $avgCost = $it->product->stocks()
                ->where('warehouse_id',$wid)
                ->value('avg_cost') ?? 0;

            $this->adjust(
                $cid, $wid, $pid,
                -$qty, (float)$avgCost,
                'sale','sales',$sale->id
            );
        }
        $sale->status = 'posted';
        $sale->save();

        // 🔹 Activity log
        activity()
            ->performedOn($sale)
            ->causedBy(auth()->user())
            ->withProperties(['sale_id' => $sale->id])
            ->log('sale posted');
    }

    public function postTransfer(Transfer $transfer): void
    {
        if ($transfer->status === 'received') return;

        foreach ($transfer->items as $it) {
            $cid = $transfer->company_id;
            $from = $transfer->from_warehouse_id;
            $to   = $transfer->to_warehouse_id;
            $pid  = $it->product_id;
            $qty  = (float)$it->quantity;

            $avg = Stock::where('company_id',$cid)
                ->where('warehouse_id',$from)
                ->where('product_id',$pid)
                ->value('avg_cost') ?? 0;

            if (!config('inventory.allow_negative_stock')) {
                $current = Stock::where('company_id',$cid)
                    ->where('warehouse_id',$from)
                    ->where('product_id',$pid)
                    ->value('qty_on_hand') ?? 0;

                if ($current < $qty) {
                    throw new \RuntimeException("Insufficient stock for product ID {$pid} in WH {$from}");
                }
            }

            $this->adjust(
                $cid, $from, $pid,
                -$qty, (float)$avg, 'xfer_out','transfers',$transfer->id
            );

            $this->adjust(
                $cid, $to, $pid,
                +$qty, (float)$avg, 'xfer_in','transfers',$transfer->id
            );
        }
        $transfer->status = 'received';
        $transfer->save();

        // 🔹 Activity log
        activity()
            ->performedOn($transfer)
            ->causedBy(auth()->user())
            ->withProperties(['transfer_id' => $transfer->id])
            ->log('transfer posted');
    }

    public function postAdjustment(Adjustment $adj): void
    {
        if (in_array($adj->type, ['increase','decrease']) === false) return;

        foreach ($adj->items as $it) {
            $avg = Stock::where('company_id',$adj->company_id)
                ->where('warehouse_id',$adj->warehouse_id)
                ->where('product_id',$it->product_id)
                ->value('avg_cost') ?? 0;

            if ($adj->type === 'increase') {
                $unitCost = $it->unit_cost ?? $avg;
                $this->adjust(
                    $adj->company_id, $adj->warehouse_id, $it->product_id,
                    + (float)$it->quantity, (float)$unitCost, 'adjust_in','adjustments',$adj->id
                );
            } else {
                $this->adjust(
                    $adj->company_id, $adj->warehouse_id, $it->product_id,
                    - (float)$it->quantity, (float)$avg, 'adjust_out','adjustments',$adj->id
                );
            }
        }

        // 🔹 Activity log
        activity()
            ->performedOn($adj)
            ->causedBy(auth()->user())
            ->withProperties(['adjustment_id' => $adj->id])
            ->log('adjustment posted');
    }

    public function postSalesReturn(\App\Models\SalesReturn $sr): void
    {
        if ($sr->status === 'posted') return;

        $cid = $sr->company_id;
        $wid = $sr->warehouse_id;

        foreach ($sr->items as $it) {
            $pid = $it->product_id;
            $qty = (float)$it->quantity;

            $stock = \App\Models\Stock::firstOrCreate(
                ['company_id'=>$cid,'warehouse_id'=>$wid,'product_id'=>$pid],
                ['qty_on_hand'=>0,'avg_cost'=>0]
            );

            $unitCost = $it->unit_cost ?? (float)$stock->avg_cost;

            $oldQty  = (float)$stock->qty_on_hand;
            $oldCost = (float)$stock->avg_cost;
            $newQty  = $oldQty + $qty;
            $newAvg  = $newQty > 0 ? (($oldQty*$oldCost) + ($qty*$unitCost)) / $newQty : $oldCost;

            $stock->qty_on_hand = $newQty;
            $stock->avg_cost    = $newAvg;
            $stock->save();

            \App\Models\StockMovement::create([
                'company_id'=>$cid,
                'warehouse_id'=>$wid,
                'product_id'=>$pid,
                'ref_type'=>'sales_return',
                'ref_id'=>$sr->id,
                'quantity'=>$qty,
                'unit_cost'=>$unitCost,
                'direction'=>'in',
                'moved_at'=>now(),
                'note'=>'Sales return posted',
            ]);
        }

        $sr->update(['status'=>'posted','posted_at'=>now(),'posted_by'=>auth()->id()]);

        if ($sr->sale) {
            $sale = $sr->sale->refresh();
            $returnedTotal = (float)($sale->returned_total ?? 0) + (float)$sr->total;
            $paid   = (float)($sale->paid ?? 0);
            $due    = max(0, ((float)$sale->total) - $paid - $returnedTotal);
            $sale->update(['returned_total'=>$returnedTotal, 'due'=>$due]);
        }

        // 🔹 Activity log
        activity()
            ->performedOn($sr)
            ->causedBy(auth()->user())
            ->withProperties(['sales_return_id' => $sr->id])
            ->log('sales return posted');
    }

    public function postPurchaseReturn(\App\Models\PurchaseReturn $pr): void
    {
        if ($pr->status === 'posted') return;

        $cid = $pr->company_id;
        $wid = $pr->warehouse_id;

        foreach ($pr->items as $it) {
            $pid = $it->product_id;
            $qty = (float)$it->quantity;

            $stock = \App\Models\Stock::firstOrCreate(
                ['company_id'=>$cid,'warehouse_id'=>$wid,'product_id'=>$pid],
                ['qty_on_hand'=>0,'avg_cost'=>0]
            );

            if (!config('inventory.allow_negative_stock', false)) {
                if ($stock->qty_on_hand < $qty) {
                    throw new \RuntimeException("Insufficient stock for product ID {$pid} in WH {$wid} for purchase return.");
                }
            }

            $unitCost = $it->unit_cost ?? (float)$stock->avg_cost;

            $stock->qty_on_hand = (float)$stock->qty_on_hand - $qty;
            $stock->save();

            \App\Models\StockMovement::create([
                'company_id'=>$cid,
                'warehouse_id'=>$wid,
                'product_id'=>$pid,
                'ref_type'=>'purchase_return',
                'ref_id'=>$pr->id,
                'quantity'=>$qty,
                'unit_cost'=>$unitCost,
                'direction'=>'out',
                'moved_at'=>now(),
                'note'=>'Purchase return posted',
            ]);
        }

        $pr->update(['status'=>'posted','posted_at'=>now(),'posted_by'=>auth()->id()]);

        if ($pr->purchase) {
            $p = $pr->purchase->refresh();
            $returnedTotal = (float)($p->returned_total ?? 0) + (float)$pr->total;
            $paid = (float)($p->paid ?? 0);
            $due  = max(0, ((float)$p->total) - $paid - $returnedTotal);
            $p->update(['returned_total'=>$returnedTotal, 'due'=>$due]);
        }

        // 🔹 Activity log
        activity()
            ->performedOn($pr)
            ->causedBy(auth()->user())
            ->withProperties(['purchase_return_id' => $pr->id])
            ->log('purchase return posted');
    }

    public function applyStockCount(\App\Models\StockCount $sc): void
    {
        if ($sc->status === 'applied') return;

        $cid = $sc->company_id;
        $wid = $sc->warehouse_id;

        $increase = 0.0;
        $decrease = 0.0;

        foreach ($sc->items as $it) {
            $pid = $it->product_id;
            $diff = (float) $it->diff_qty;
            if ($diff == 0.0) continue;

            $stock = \App\Models\Stock::firstOrCreate(
                ['company_id'=>$cid,'warehouse_id'=>$wid,'product_id'=>$pid],
                ['qty_on_hand'=>0,'avg_cost'=>0]
            );

            $unitCost = $it->unit_cost ?? (float) $stock->avg_cost;

            if ($diff > 0) {
                $oldQty = (float) $stock->qty_on_hand;
                $oldCost = (float) $stock->avg_cost;
                $newQty = $oldQty + $diff;
                $newAvg = $newQty > 0 ? (($oldQty*$oldCost) + ($diff*$unitCost)) / $newQty : $oldCost;

                $stock->qty_on_hand = $newQty;
                $stock->avg_cost = $newAvg;
                $stock->save();

                \App\Models\StockMovement::create([
                    'company_id'=>$cid,'warehouse_id'=>$wid,'product_id'=>$pid,
                    'ref_type'=>'stock_count','ref_id'=>$sc->id,
                    'quantity'=>$diff,'unit_cost'=>$unitCost,'direction'=>'in',
                    'moved_at'=>now(),'note'=>'Stock count apply (increase)',
                ]);

                $increase += $diff * $unitCost;

            } else {
                $qtyOut = abs($diff);

                if (!config('inventory.allow_negative_stock', false) && $stock->qty_on_hand < $qtyOut) {
                    throw new \RuntimeException("Insufficient stock for product {$pid} to apply stock count.");
                }

                $stock->qty_on_hand = (float) $stock->qty_on_hand - $qtyOut;
                $stock->save();

                \App\Models\StockMovement::create([
                    'company_id'=>$cid,'warehouse_id'=>$wid,'product_id'=>$pid,
                    'ref_type'=>'stock_count','ref_id'=>$sc->id,
                    'quantity'=>$qtyOut,'unit_cost'=>$unitCost,'direction'=>'out',
                    'moved_at'=>now(),'note'=>'Stock count apply (decrease)',
                ]);

                $decrease += $qtyOut * $unitCost;
            }
        }

        $sc->update([
            'status'=>'applied',
            'increase_value'=>$increase,
            'decrease_value'=>$decrease,
            'applied_at'=>now(),
            'applied_by'=>auth()->id(),
        ]);

        // 🔹 Activity log
        activity()
            ->performedOn($sc)
            ->causedBy(auth()->user())
            ->withProperties(['stock_count_id' => $sc->id])
            ->log('stock count applied');
    }
}
