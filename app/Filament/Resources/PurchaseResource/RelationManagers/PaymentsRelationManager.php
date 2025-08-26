<?php

namespace App\Filament\Resources\PurchaseResource\RelationManagers;

use App\Models\PaymentMethod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';
    protected static ?string $title = 'Payments';

    // ❌ static নয়
    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Hidden::make('company_id')->default(fn () => auth()->user()->company_id),
            Forms\Components\Select::make('method_id')->label('Method')
                ->options(fn () => PaymentMethod::where('company_id', auth()->user()->company_id)->pluck('name', 'id'))
                ->required()->searchable(),
            Forms\Components\TextInput::make('amount')->numeric()->required(),
            Forms\Components\TextInput::make('reference')->label('Ref/Txn')->maxLength(50),
            Forms\Components\DateTimePicker::make('paid_at')->default(now())->required(),
            Forms\Components\Textarea::make('note')->columnSpanFull(),
        ])->columns(2);
    }

    // ❌ static নয়
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('method.name')->label('Method'),
                Tables\Columns\TextColumn::make('amount')->money('bdt', true),
                Tables\Columns\TextColumn::make('paid_at')->dateTime(),
                Tables\Columns\TextColumn::make('reference')->label('Ref')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Tables\Actions\Action::make('pay')
                    ->label('Pay Supplier')
                    ->icon('heroicon-o-banknotes')
                    ->form([
                        Forms\Components\Select::make('method_id')
                            ->label('Method')->required()
                            ->options(fn () => PaymentMethod::where('company_id', auth()->user()->company_id)->pluck('name', 'id')),
                        Forms\Components\TextInput::make('amount')->numeric()->required(),
                        Forms\Components\TextInput::make('reference')->label('Ref/Txn')->maxLength(50),
                        Forms\Components\DateTimePicker::make('paid_at')->default(now())->required(),
                        Forms\Components\Textarea::make('note'),
                    ])
                    ->action(function (array $data) {
                        $purchase = $this->getOwnerRecord(); // parent Purchase
                        app(\App\Services\PaymentService::class)
                            ->payPurchase($purchase, $data['method_id'], (float) $data['amount'], $data['reference'] ?? null, $data['note'] ?? null, $data['paid_at'] ?? now());
                    })
                    ->successNotificationTitle('Payment recorded'),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('id', 'desc');
    }
}
