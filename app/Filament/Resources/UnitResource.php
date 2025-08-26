<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasCrudPermissions;
use App\Filament\Concerns\TenantQuery;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UnitResource extends Resource
{
    use TenantQuery, HasCrudPermissions;

    protected static ?string $model = Unit::class;
    protected static ?string $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationGroup = 'Masters';
    protected static ?int $navigationSort = 40;
    protected static string $permissionPrefix = 'units';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Hidden::make('company_id')->default(fn()=>auth()->user()->company_id),
            Forms\Components\TextInput::make('short_name')->label('Short')->required()->maxLength(20),
            Forms\Components\TextInput::make('name')->required()->maxLength(50),
            Forms\Components\TextInput::make('precision')->numeric()->default(0),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('short_name')->sortable(),
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\TextColumn::make('precision'),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => UnitResource\Pages\ListUnits::route('/'),
            'create' => UnitResource\Pages\CreateUnit::route('/create'),
            'edit' => UnitResource\Pages\EditUnit::route('/{record}/edit'),
        ];
    }
}
