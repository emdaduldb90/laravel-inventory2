<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasCrudPermissions;
use App\Filament\Concerns\TenantQuery;
use App\Models\Brand;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BrandResource extends Resource
{
    use TenantQuery, HasCrudPermissions;

    protected static ?string $model = Brand::class;
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationGroup = 'Masters';
    protected static ?int $navigationSort = 30;
    protected static string $permissionPrefix = 'brands';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Hidden::make('company_id')->default(fn()=>auth()->user()->company_id),
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('slug')->required()->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('slug')->searchable(),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => BrandResource\Pages\ListBrands::route('/'),
            'create' => BrandResource\Pages\CreateBrand::route('/create'),
            'edit' => BrandResource\Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
