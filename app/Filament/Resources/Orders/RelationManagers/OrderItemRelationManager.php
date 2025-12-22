<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;

class OrderItemRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('dish.name')->label('Platillo'),
                TextColumn::make('quantity')->label('Cantidad'),
                TextColumn::make('unit_price')->label('Precio Unitario')->money('MXN'),
                TextColumn::make('total_price')->label('Total')->money('MXN'),
            ]);
    }
}