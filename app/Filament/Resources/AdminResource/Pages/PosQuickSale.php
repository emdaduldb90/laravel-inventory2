<?php

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\Resources\AdminResource;
use Filament\Resources\Pages\Page;

class PosQuickSale extends Page
{
    protected static string $resource = AdminResource::class;

    protected static string $view = 'filament.resources.admin-resource.pages.pos-quick-sale';
}
