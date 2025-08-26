<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdjustmentResource\Pages;
use App\Models\Adjustment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AdjustmentResource extends \Filament\Resources\Resource
{
    protected static ?string $model = Adjustment::class;
    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static ?string $navigationGroup = 'Operations';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('company_id', auth()->user()->company_id);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Adjustment')
                ->columns(3)
                ->schema([
                    Forms\Components\Hidden::make('company_id')->default(fn () => auth()->user()->company_id),
                    Forms\Components\TextInput::make('adj_no')->label('Adj No.')->maxLength(50),
                    Forms\Components\Select::make('warehouse_id')
                        ->relationship('warehouse','code', modifyQueryUsing: fn ($q) => $q->where('company_id', auth()->user()->company_id))
                        ->required()->searchable(),
                    Forms\Components\Select::make('type')
                        ->options(['increase'=>'Increase','decrease'=>'Decrease'])
                        ->default('decrease')->required(),
                    Forms\Components\DatePicker::make('adj_date')->default(now()),
                    Forms\Components\TextInput::make('reason')->maxLength(100)->columnSpanFull(),
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
                            Forms\Components\TextInput::make('unit_cost')
                                ->numeric()->default(0)
                                ->helperText('Increase হলে কস্ট চাইলে দিন'),
                        ])
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('adj_no')->label('ADJ'),
                Tables\Columns\TextColumn::make('warehouse.code')->label('WH'),
                Tables\Columns\BadgeColumn::make('type')->colors([
                    'success' => 'increase',
                    'danger'  => 'decrease',
                ]),
                Tables\Columns\TextColumn::make('adj_date')->date(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('apply')
                    ->label('Apply')
                    ->requiresConfirmation()
                    ->action(fn ($record) => app(\App\Services\InventoryService::class)->postAdjustment($record))
                    ->successNotificationTitle('Adjustment applied'),
            ])
            ->defaultSort('id','desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAdjustments::route('/'),
            'create' => Pages\CreateAdjustment::route('/create'),
            'edit'   => Pages\EditAdjustment::route('/{record}/edit'),
        ];
    }
}
