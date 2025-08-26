<?php

namespace App\Filament\Resources;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Spatie\Activitylog\Models\Activity;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;
    protected static ?string $navigationGroup = 'Reports';
    protected static ?string $navigationIcon  = 'heroicon-o-clipboard-document';
    protected static ?string $navigationLabel = 'Activity Log';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('log_name')->label('Log'),
                Tables\Columns\TextColumn::make('description')->label('Action')->wrap(),
                Tables\Columns\TextColumn::make('causer.name')->label('By'),
                Tables\Columns\TextColumn::make('subject_type')->label('Subject'),
                Tables\Columns\TextColumn::make('subject_id')->label('ID'),
            ])
            ->defaultSort('id','desc');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('reports.view') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ActivityLogResource\Pages\ListActivities::route('/'),
        ];
    }
}
