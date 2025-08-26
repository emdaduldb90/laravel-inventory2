<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseReturnResource\Pages;
use App\Models\PurchaseReturn;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PurchaseReturnResource extends \Filament\Resources\Resource
{
    protected static ?string $model = PurchaseReturn::class;
    protected static ?string $navigationGroup = 'Operations';
    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-up';
    protected static ?string $navigationLabel = 'Purchase Returns';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('company_id', auth()->user()->company_id);
    }

    public static function canViewAny(): bool { return true; }
    public static function canCreate(): bool  { return true; }
    public static function canEdit($record): bool  { return true; }
    public static function canDelete($record): bool { return true; }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Return Info')
                ->columns(3)
                ->schema([
                    Forms\Components\Hidden::make('company_id')
                        ->default(fn () => auth()->user()->company_id),

                    Forms\Components\TextInput::make('pr_number')
                        ->label('PR No.'),

                    Forms\Components\Select::make('purchase_id')
                        ->relationship(
                            name: 'purchase',
                            titleAttribute: 'po_number',
                            modifyQueryUsing: fn (Builder $query) =>
                                $query->where('company_id', auth()->user()->company_id)
                        )
                        ->searchable()
                        ->preload()
                        ->label('Original PO'),

                    Forms\Components\Select::make('supplier_id')
                        ->relationship(
                            name: 'supplier',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn (Builder $query) =>
                                $query->where('company_id', auth()->user()->company_id)
                        )
                        ->searchable()
                        ->required(),

                    Forms\Components\Select::make('warehouse_id')
                        ->relationship(
                            name: 'warehouse',
                            titleAttribute: 'code',
                            modifyQueryUsing: fn (Builder $query) =>
                                $query->where('company_id', auth()->user()->company_id)
                        )
                        ->searchable()
                        ->required(),

                    Forms\Components\Select::make('status')
                        ->options(['draft' => 'Draft', 'posted' => 'Posted'])
                        ->default('draft')
                        ->required(),

                    Forms\Components\DatePicker::make('return_date')
                        ->default(now())
                        ->required(),

                    Forms\Components\TextInput::make('subtotal')
                        ->numeric()
                        ->readOnly()
                        ->dehydrateStateUsing(
                            fn (Get $get) =>
                                collect($get('items') ?? [])
                                    ->sum(fn ($i) => (float) ($i['line_total'] ?? 0))
                        ),

                    Forms\Components\TextInput::make('discount')
                        ->numeric()
                        ->default(0)
                        ->live()
                        ->afterStateUpdated(
                            fn (Set $set, Get $get) =>
                                $set('total', (float) $get('subtotal') - (float) $get('discount') + (float) $get('tax'))
                        ),

                    Forms\Components\TextInput::make('tax')
                        ->numeric()
                        ->default(0)
                        ->live()
                        ->afterStateUpdated(
                            fn (Set $set, Get $get) =>
                                $set('total', (float) $get('subtotal') - (float) $get('discount') + (float) $get('tax'))
                        ),

                    Forms\Components\TextInput::make('total')
                        ->numeric()
                        ->readOnly(),

                    Forms\Components\Textarea::make('note')->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Items')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship()
                        ->defaultItems(1)
                        ->columns(5)
                        ->live()
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            $sum = collect($get('items') ?? [])
                                ->sum(fn ($i) => (float) ($i['line_total'] ?? 0));
                            $set('subtotal', $sum);
                            $set('total', $sum - (float) $get('discount') + (float) $get('tax'));
                        })
                        ->schema([
                            Forms\Components\Hidden::make('company_id')
                                ->default(fn () => auth()->user()->company_id),

                            Forms\Components\Select::make('product_id')
                                ->relationship(
                                    name: 'product',
                                    titleAttribute: 'name',
                                    modifyQueryUsing: fn (Builder $query) =>
                                        $query->where('company_id', auth()->user()->company_id)
                                )
                                ->required()
                                ->searchable()
                                ->columnSpan(2),

                            Forms\Components\TextInput::make('quantity')
                                ->numeric()
                                ->default(1)
                                ->live()
                                ->afterStateUpdated(
                                    fn (Set $set, Get $get) =>
                                        $set('line_total', (float) $get('quantity') * (float) $get('unit_cost'))
                                ),

                            Forms\Components\TextInput::make('unit_cost')
                                ->numeric()
                                ->default(0)
                                ->live()
                                ->afterStateUpdated(
                                    fn (Set $set, Get $get) =>
                                        $set('line_total', (float) $get('quantity') * (float) $get('unit_cost'))
                                ),

                            Forms\Components\TextInput::make('line_total')
                                ->numeric()
                                ->readOnly(),
                        ])
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pr_number')->label('PR')->searchable(),
                Tables\Columns\TextColumn::make('purchase.po_number')->label('PO')->searchable(),
                Tables\Columns\TextColumn::make('supplier.name')->label('Supplier')->searchable(),
                Tables\Columns\TextColumn::make('warehouse.code')->label('WH'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => $state === 'posted' ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('total')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
                Tables\Columns\TextColumn::make('return_date')->date(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('post')
                    ->visible(fn ($record) => $record->status !== 'posted')
                    ->label('Post')
                    ->requiresConfirmation()
                    ->action(fn ($record) => app(\App\Services\InventoryService::class)->postPurchaseReturn($record))
                    ->successNotificationTitle('Purchase return posted'),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPurchaseReturns::route('/'),
            'create' => Pages\CreatePurchaseReturn::route('/create'),
            'edit'   => Pages\EditPurchaseReturn::route('/{record}/edit'),
        ];
    }
}
