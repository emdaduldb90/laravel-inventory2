<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransferResource\Pages;
use App\Models\Transfer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransferResource extends \Filament\Resources\Resource
{
    protected static ?string $model = Transfer::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationGroup = 'Operations';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('company_id', auth()->user()->company_id);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Transfer')
                ->columns(3)
                ->schema([
                    Forms\Components\Hidden::make('company_id')->default(fn () => auth()->user()->company_id),
                    Forms\Components\TextInput::make('transfer_no')->label('Transfer No.')->maxLength(50),
                    Forms\Components\Select::make('from_warehouse_id')
                        ->relationship('from', 'code', modifyQueryUsing: fn ($q) => $q->where('company_id', auth()->user()->company_id))
                        ->required()->searchable()->label('From WH'),
                    Forms\Components\Select::make('to_warehouse_id')
                        ->relationship('to', 'code', modifyQueryUsing: fn ($q) => $q->where('company_id', auth()->user()->company_id))
                        ->required()->searchable()->label('To WH'),
                    Forms\Components\Select::make('status')
                        ->options(['draft'=>'Draft','sent'=>'Sent','received'=>'Received'])->default('sent')->required(),
                    Forms\Components\DatePicker::make('transfer_date')->default(now()),
                    Forms\Components\Textarea::make('note')->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Items')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship()
                        ->columns(3)
                        ->defaultItems(1)
                        ->schema([
                            Forms\Components\Select::make('product_id')
                                ->relationship('product','name', modifyQueryUsing: fn ($q) => $q->where('company_id', auth()->user()->company_id))
                                ->required()->searchable()->columnSpan(2),
                            Forms\Components\TextInput::make('quantity')->numeric()->default(1)->required(),
                        ])
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transfer_no')->label('TR'),
                Tables\Columns\TextColumn::make('from.code')->label('From'),
                Tables\Columns\TextColumn::make('to.code')->label('To'),
                Tables\Columns\BadgeColumn::make('status')->colors([
                    'warning' => 'sent',
                    'success' => 'received',
                ]),
                Tables\Columns\TextColumn::make('transfer_date')->date(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('complete')
                    ->label('Complete')
                    ->visible(fn ($record) => $record->status !== 'received')
                    ->requiresConfirmation()
                    ->action(fn ($record) => app(\App\Services\InventoryService::class)->postTransfer($record))
                    ->successNotificationTitle('Transfer completed'),
            ])
            ->defaultSort('id','desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTransfers::route('/'),
            'create' => Pages\CreateTransfer::route('/create'),
            'edit'   => Pages\EditTransfer::route('/{record}/edit'),
        ];
    }
}
