<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockCountResource\Pages;
use App\Models\Stock;
use App\Models\StockCount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockCountResource extends \Filament\Resources\Resource
{
    protected static ?string $model = StockCount::class;
    protected static ?string $navigationGroup = 'Operations';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Stock Counts';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('company_id', auth()->user()->company_id);
    }

    public static function canViewAny(): bool { return true; }
    public static function canCreate(): bool  { return true; }
    public static function canEdit($r): bool  { return true; }
    public static function canDelete($r): bool{ return true; }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Count Sheet')
                ->columns(3)
                ->schema([
                    Forms\Components\Hidden::make('company_id')
                        ->default(fn()=>auth()->user()->company_id),

                    Forms\Components\TextInput::make('sc_number')
                        ->label('SC No.'),

                    Forms\Components\Select::make('warehouse_id')
                        ->relationship(
                            name: 'warehouse',
                            titleAttribute: 'code',
                            modifyQueryUsing: fn(Builder $q) =>
                                $q->where('company_id', auth()->user()->company_id)
                        )
                        ->required()->searchable(),

                    Forms\Components\Select::make('status')
                        ->options(['draft'=>'Draft','applied'=>'Applied'])
                        ->default('draft')->required(),

                    Forms\Components\DatePicker::make('count_date')
                        ->default(now())->required(),

                    Forms\Components\TextInput::make('increase_value')
                        ->numeric()->readOnly(),

                    Forms\Components\TextInput::make('decrease_value')
                        ->numeric()->readOnly(),

                    Forms\Components\Textarea::make('note')->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Lines')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship()
                        ->defaultItems(1)
                        ->columns(7)
                        ->live()
                        ->schema([
                            Forms\Components\Hidden::make('company_id')
                                ->default(fn()=>auth()->user()->company_id),

                            Forms\Components\Select::make('product_id')
                                ->relationship(
                                    name: 'product',
                                    titleAttribute: 'name',
                                    modifyQueryUsing: fn(Builder $q) =>
                                        $q->where('company_id', auth()->user()->company_id)
                                )
                                ->required()->searchable()->columnSpan(3)
                                ->afterStateUpdated(function(Set $set, Get $get, $state) {
                                    $formState = $get(null);
                                    $wid = $formState['warehouse_id'] ?? null;
                                    if ($state && $wid) {
                                        $systemQty = (float) Stock::where('company_id', auth()->user()->company_id)
                                            ->where('warehouse_id', $wid)->where('product_id', $state)
                                            ->value('qty_on_hand') ?? 0;
                                        $avgCost = (float) Stock::where('company_id', auth()->user()->company_id)
                                            ->where('warehouse_id', $wid)->where('product_id', $state)
                                            ->value('avg_cost') ?? 0;
                                        $set('system_qty', $systemQty);
                                        if (! $get('unit_cost')) $set('unit_cost', $avgCost);
                                        $counted = (float) ($get('counted_qty') ?? 0);
                                        $set('diff_qty', $counted - $systemQty);
                                        $set('value_diff', ($counted - $systemQty) * (float)($get('unit_cost') ?? $avgCost));
                                    }
                                }),

                            Forms\Components\TextInput::make('system_qty')
                                ->numeric()->readOnly(),

                            Forms\Components\TextInput::make('counted_qty')
                                ->numeric()->default(0)->live()
                                ->afterStateUpdated(function(Set $set, Get $get){
                                    $sys = (float) ($get('system_qty') ?? 0);
                                    $cnt = (float) ($get('counted_qty') ?? 0);
                                    $uc  = (float) ($get('unit_cost') ?? 0);
                                    $diff = $cnt - $sys;
                                    $set('diff_qty', $diff);
                                    $set('value_diff', $diff * $uc);
                                }),

                            Forms\Components\TextInput::make('diff_qty')
                                ->numeric()->readOnly(),

                            Forms\Components\TextInput::make('unit_cost')
                                ->numeric()->live()
                                ->afterStateUpdated(function(Set $set, Get $get){
                                    $diff = (float) ($get('diff_qty') ?? 0);
                                    $uc   = (float) ($get('unit_cost') ?? 0);
                                    $set('value_diff', $diff * $uc);
                                }),

                            Forms\Components\TextInput::make('value_diff')
                                ->numeric()->readOnly(),
                        ])
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            $inc = 0.0; $dec = 0.0;
                            foreach (($get('items') ?? []) as $row) {
                                $v = (float) ($row['value_diff'] ?? 0);
                                if ($v >= 0) $inc += $v; else $dec += abs($v);
                            }
                            $set('increase_value', $inc);
                            $set('decrease_value', $dec);
                        })
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
                Tables\Columns\TextColumn::make('sc_number')->label('SC')->searchable(),
                Tables\Columns\TextColumn::make('warehouse.code')->label('WH'),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn(string $s)=>$s==='applied'?'success':'warning'),
                Tables\Columns\TextColumn::make('increase_value')
                    ->formatStateUsing(fn($v)=>number_format((float)$v,2)),
                Tables\Columns\TextColumn::make('decrease_value')
                    ->formatStateUsing(fn($v)=>number_format((float)$v,2)),
                Tables\Columns\TextColumn::make('count_date')->date(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('apply')
                    ->visible(fn($record)=>$record->status!=='applied')
                    ->label('Apply')->color('success')->requiresConfirmation()
                    ->action(fn($record)=>app(\App\Services\InventoryService::class)->applyStockCount($record))
                    ->successNotificationTitle('Stock count applied'),
            ])
            ->defaultSort('id','desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStockCounts::route('/'),
            'create' => Pages\CreateStockCount::route('/create'),
            'edit'   => Pages\EditStockCount::route('/{record}/edit'),
        ];
    }
}
