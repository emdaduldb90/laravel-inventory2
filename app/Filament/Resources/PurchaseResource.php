<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Filament\Resources\PurchaseResource\RelationManagers\PaymentsRelationManager;
use App\Models\Purchase;
use App\Models\PaymentMethod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PurchaseResource extends \Filament\Resources\Resource
{
    protected static ?string $model = Purchase::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static ?string $navigationGroup = 'Operations';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('company_id', auth()->user()->company_id);
    }

    public static function canViewAny(): bool { return auth()->user()->can('purchase.create'); }
    public static function canCreate(): bool  { return auth()->user()->can('purchase.create'); }
    public static function canEdit($r): bool  { return auth()->user()->can('purchase.create'); }
    public static function canDelete($r): bool{ return auth()->user()->can('purchase.create'); }

    // 🔹 এখানে PaymentsRelationManager যুক্ত করলাম
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
            'index'=>Pages\ListPurchases::route('/'),
            'create'=>Pages\CreatePurchase::route('/create'),
            'edit'=>Pages\EditPurchase::route('/{record}/edit'),
        ];
    }
}
