<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;

class OrderModificationRelationManager extends RelationManager
{
    protected static string $relationship = 'modifications';

    protected static ?string $title = 'Historial de Cambios';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                BadgeColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'item_added' => 'Agregado',
                        'item_removed' => 'Eliminado',
                        'quantity_changed' => 'Cantidad',
                        default => $state,
                    })
                    ->colors([
                        'success' => 'item_added',
                        'danger' => 'item_removed',
                        'warning' => 'quantity_changed',
                    ]),
                TextColumn::make('dish_name')->label('Platillo'),
                TextColumn::make('quantity')->label('Cantidad'),
                TextColumn::make('unit_price')->label('Precio')->money('MXN'),
                TextColumn::make('total_before')->label('Total Antes')->money('MXN'),
                TextColumn::make('total_after')->label('Total Después')->money('MXN'),
                TextColumn::make('modified_by')->label('Modificado por'),
                TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
