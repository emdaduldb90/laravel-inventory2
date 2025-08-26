<?php
namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Sequence;
use App\Models\Sale;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PaymentService
{
    public function paySale(Sale $sale, int $methodId, float $amount, ?string $ref=null, ?string $note=null): Payment
    {
        if ($amount <= 0) throw new InvalidArgumentException('Amount must be positive');

        return DB::transaction(function () use ($sale,$methodId,$amount,$ref,$note) {
            $payable = max(0, (float)$sale->total - (float)$sale->paid);
            if ($amount > $payable) $amount = $payable; // cap

            $payment = Payment::create([
                'company_id'       => $sale->company_id,
                'paymentable_type' => 'sales',
                'paymentable_id'   => $sale->id,
                'method_id'        => $methodId,
                'date'             => now()->toDateString(),
                'amount'           => $amount,
                'direction'        => 'in',
                'receipt_no'       => Sequence::next('receipt',$sale->company_id,'RCV-'),
                'reference'        => $ref,
                'note'             => $note,
            ]);

            $sale->paid = (float)$sale->paid + $amount;
            $sale->due  = max(0, (float)$sale->total - (float)$sale->paid);
            $sale->save();

            return $payment;
        });
    }

    public function payPurchase(Purchase $purchase, int $methodId, float $amount, ?string $ref=null, ?string $note=null): Payment
    {
        if ($amount <= 0) throw new InvalidArgumentException('Amount must be positive');

        return DB::transaction(function () use ($purchase,$methodId,$amount,$ref,$note) {
            $payable = max(0, (float)$purchase->total - (float)$purchase->paid);
            if ($amount > $payable) $amount = $payable;

            $payment = Payment::create([
                'company_id'       => $purchase->company_id,
                'paymentable_type' => 'purchases',
                'paymentable_id'   => $purchase->id,
                'method_id'        => $methodId,
                'date'             => now()->toDateString(),
                'amount'           => $amount,
                'direction'        => 'out',
                'receipt_no'       => Sequence::next('receipt',$purchase->company_id,'PAY-'),
                'reference'        => $ref,
                'note'             => $note,
            ]);

            $purchase->paid = (float)$purchase->paid + $amount;
            $purchase->due  = max(0, (float)$purchase->total - (float)$purchase->paid);
            $purchase->save();

            return $payment;
        });
    }
}
