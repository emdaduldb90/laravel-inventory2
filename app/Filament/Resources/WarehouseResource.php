<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasCrudPermissions;
use App\Filament\Concerns\TenantQuery;
use Filament\Resources\Resource;
use App\Filament\Resources\WarehouseResource\Pages;
use App\Models\Warehouse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WarehouseResource extends Resource
{
    use TenantQuery, HasCrudPermissions;

    protected static ?string $model = Warehouse::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'Masters';
    protected static ?int $navigationSort = 10;
    protected static string $permissionPrefix = 'warehouses';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Hidden::make('company_id')
                ->default(fn()=>auth()->user()->company_id),
            Forms\Components\TextInput::make('code')->required()->maxLength(50),
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('phone')->tel()->maxLength(50),
            Forms\Components\Textarea::make('address')->rows(2),
            Forms\Components\Toggle::make('is_default'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('code')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('phone'),
            Tables\Columns\IconColumn::make('is_default')->boolean(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->toggleable(isToggledHiddenByDefault:true),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => WarehouseResource\Pages\ListWarehouses::route('/'),
            'create' => WarehouseResource\Pages\CreateWarehouse::route('/create'),
            'edit' => WarehouseResource\Pages\EditWarehouse::route('/{record}/edit'),
        ];
    }
}
