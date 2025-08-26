<?php

namespace App\Filament\Resources\StockCountResource\Pages;

use App\Filament\Resources\StockCountResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStockCount extends CreateRecord
{
    protected static string $resource = StockCountResource::class;

    // অটো SC নম্বর + company_id সেট করতে চাইলে:
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = auth()->user()->company_id ?? null;

        if (empty($data['sc_number']) && class_exists(\App\Models\Sequence::class)) {
            $data['sc_number'] = \App\Models\Sequence::next('stock_count', $data['company_id'], 'SC-');
        }

        return $data;
    }
}
