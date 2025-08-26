<?php

namespace App\Filament\Resources\PurchaseReturnResource\Pages;

use App\Filament\Resources\PurchaseReturnResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchaseReturn extends CreateRecord
{
    protected static string $resource = PurchaseReturnResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = auth()->user()->company_id;

        if (empty($data['pr_number']) && class_exists(\App\Models\Sequence::class)) {
            $data['pr_number'] = \App\Models\Sequence::next(
                'purchase_return',
                auth()->user()->company_id,
                'PR-'
            );
        }

        return $data;
    }
}