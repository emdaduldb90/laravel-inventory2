<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class QuickStats extends BaseWidget
{
    protected function getStats(): array
    {
        $cid = auth()->user()->company_id;

        $products = Product::where('company_id',$cid)->count();
        $qty      = (float) DB::table('stocks')->where('company_id',$cid)->sum('qty_on_hand');
        $value    = (float) DB::table('stocks')->where('company_id',$cid)->selectRaw('SUM(qty_on_hand*avg_cost) v')->value('v');

        return [
            Stat::make('Products', $products),
            Stat::make('Stock Qty', number_format($qty,2)),
            Stat::make('Stock Value', number_format($value,2)),
        ];
    }
}
