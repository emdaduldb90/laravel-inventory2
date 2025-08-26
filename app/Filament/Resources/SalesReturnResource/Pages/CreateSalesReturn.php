<?php

namespace App\Filament\Resources\SalesReturnResource\Pages;

use App\Filament\Resources\SalesReturnResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSalesReturn extends CreateRecord
{
    protected static string $resource = SalesReturnResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = auth()->user()->company_id;

        if (empty($data['sr_number']) && class_exists(\App\Models\Sequence::class)) {
            $data['sr_number'] = \App\Models\Sequence::next(
                'sales_return',
                auth()->user()->company_id,
                'SR-'
            );
        }

        return $data;
    }
}

