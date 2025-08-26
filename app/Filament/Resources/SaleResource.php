<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Filament\Resources\SaleResource\RelationManagers\PaymentsRelationManager;
use App\Models\Sale;
use App\Models\PaymentMethod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SaleResource extends \Filament\Resources\Resource
{
    protected static ?string $model = Sale::class;
    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationGroup = 'Operations';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('company_id', auth()->user()->company_id);
    }

    public static function canViewAny(): bool { return auth()->user()->can('sale.create'); }
    public static function canCreate(): bool  { return auth()->user()->can('sale.create'); }
    public static function canEdit($r): bool  { return auth()->user()->can('sale.create'); }
    public static function canDelete($r): bool{ return auth()->user()->can('sale.create'); }

    // 🔹 এখানে Relation Manager রেজিস্টার করা হলো
    public static function getRelations(): array
    {
        return [
            PaymentsRelationManager::class,
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            // ... তোমার আগের ফর্ম কোড ...
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            // ... তোমার আগের টেবিল কোড ...
        ])->actions([
            // ... তোমার আগের actions ...
        ])->defaultSort('id','desc');
    }

    public static function getPages(): array
    {
        return [
            'index'=>Pages\ListSales::route('/'),
            'create'=>Pages\CreateSale::route('/create'),
            'edit'=>Pages\EditSale::route('/{record}/edit'),
        ];
    }
}
