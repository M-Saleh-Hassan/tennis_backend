<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $recordTitleAttribute = 'id';

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('tennis_court_id')
                ->relationship('tennisCourt', 'name')
                ->required(),
            Forms\Components\DateTimePicker::make('start_time')
                ->required(),
            Forms\Components\DateTimePicker::make('end_time')
                ->required(),
            Forms\Components\TextInput::make('price')
                ->required()
                ->numeric(),
        ];
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('tennisCourt.name'),
            Tables\Columns\TextColumn::make('start_time')
                ->dateTime(),
            Tables\Columns\TextColumn::make('end_time')
                ->dateTime(),
            Tables\Columns\TextColumn::make('price')
                ->money('egp'),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ];
    }
}
