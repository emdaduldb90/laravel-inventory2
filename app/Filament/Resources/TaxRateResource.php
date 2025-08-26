<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasCrudPermissions;
use App\Filament\Concerns\TenantQuery;
use App\Models\TaxRate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TaxRateResource extends Resource
{
    use TenantQuery, HasCrudPermissions;

    protected static ?string $model = TaxRate::class;
    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationGroup = 'Masters';
    protected static ?int $navigationSort = 50;
    protected static string $permissionPrefix = 'tax-rates';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Hidden::make('company_id')->default(fn()=>auth()->user()->company_id),
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('rate')->numeric()->suffix('%')->required(),
            Forms\Components\Toggle::make('inclusive')->label('Inclusive?'),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->sortable(),
            Tables\Columns\TextColumn::make('rate')->suffix('%'),
            Tables\Columns\IconColumn::make('inclusive')->boolean(),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => TaxRateResource\Pages\ListTaxRates::route('/'),
            'create' => TaxRateResource\Pages\CreateTaxRate::route('/create'),
            'edit' => TaxRateResource\Pages\EditTaxRate::route('/{record}/edit'),
        ];
    }
}
