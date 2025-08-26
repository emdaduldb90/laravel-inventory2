<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentMethodResource\Pages;
use App\Models\PaymentMethod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PaymentMethodResource extends \Filament\Resources\Resource
{
    protected static ?string $model = PaymentMethod::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Master';

    public static function getEloquentQuery(): Builder
    { return parent::getEloquentQuery()->where('company_id', auth()->user()->company_id); }

    // permissions
    public static function canViewAny(): bool { return auth()->user()->can('payment.receive') || auth()->user()->can('payment.pay'); }
    public static function canCreate(): bool  { return auth()->user()->can('settings.manage'); }
    public static function canEdit($r): bool  { return auth()->user()->can('settings.manage'); }
    public static function canDelete($r): bool{ return auth()->user()->can('settings.manage'); }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Hidden::make('company_id')->default(fn()=>auth()->user()->company_id),
            Forms\Components\TextInput::make('name')->required()->maxLength(100),
            Forms\Components\Select::make('type')->options([
                'cash'=>'Cash','mobile'=>'Mobile','bank'=>'Bank',
            ])->default('cash')->required(),
            Forms\Components\TextInput::make('account')->label('Account/No.')->maxLength(100),
            Forms\Components\Textarea::make('note')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\TextColumn::make('type'),
            Tables\Columns\TextColumn::make('account')->toggleable(isToggledHiddenByDefault:true),
        ])->actions([Tables\Actions\EditAction::make()])
          ->defaultSort('id','desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPaymentMethods::route('/'),
            'create' => Pages\CreatePaymentMethod::route('/create'),
            'edit'   => Pages\EditPaymentMethod::route('/{record}/edit'),
        ];
    }
}
