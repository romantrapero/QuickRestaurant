<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\SelectColumn;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('# de Orden')
                    ->searchable(),
                TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable(),
                BadgeColumn::make('status_badge')
                    ->label('')
                    ->getStateUsing(fn($record) => $record->status)
                    ->colors([
                        'warning' => static fn ($state): bool => $state === 'pending',
                        'info' => static fn ($state): bool => $state === 'preparing',
                        'secondary' => static fn ($state): bool => $state === 'ready',
                        'success' => static fn ($state): bool => $state === 'delivered',
                        'danger' => static fn ($state): bool => $state === 'cancelled',
                    ])
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'pending' => 'Pendiente',
                            'preparing' => 'Preparando',
                            'ready' => 'Listo',
                            'delivered' => 'Entregada',
                            'cancelled' => 'Cancelada',
                            default => $state,
                        };
                    }),
                SelectColumn::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'preparing' => 'Preparando',
                        'ready' => 'Listo',
                        'delivered' => 'Entregada',
                        'cancelled' => 'Cancelada',
                    ])
                    ->sortable(),
                TextColumn::make('total')
                    ->label('Total')
                    ->formatStateUsing(fn($state) => '$' . number_format($state, 2))
                    ->sortable(),
                TextColumn::make('table_number')
                    ->label('Tipo de Orden')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
