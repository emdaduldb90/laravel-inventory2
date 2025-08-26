<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasCrudPermissions;
use App\Filament\Concerns\TenantQuery;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SupplierResource extends Resource
{
    use TenantQuery, HasCrudPermissions;

    protected static ?string $model = Supplier::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Masters';
    protected static ?int $navigationSort = 60;
    protected static string $permissionPrefix = 'suppliers';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Hidden::make('company_id')->default(fn()=>auth()->user()->company_id),
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('phone')->tel(),
            Forms\Components\TextInput::make('email')->email(),
            Forms\Components\Textarea::make('address')->rows(2),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\TextColumn::make('phone'),
            Tables\Columns\TextColumn::make('email'),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => SupplierResource\Pages\ListSuppliers::route('/'),
            'create' => SupplierResource\Pages\CreateSupplier::route('/create'),
            'edit' => SupplierResource\Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
