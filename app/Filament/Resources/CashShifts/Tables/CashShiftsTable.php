<?php

namespace App\Filament\Resources\CashShifts\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CashShiftsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('opened_at')
                    ->label('Apertura')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('closed_at')
                    ->label('Cierre')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('En curso')
                    ->sortable(),

                TextColumn::make('is_open')
                    ->label('Estado')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->is_open ? 'Abierto' : 'Cerrado')
                    ->color(fn ($state) => $state === 'Abierto' ? 'success' : 'gray'),

                TextColumn::make('opened_by')
                    ->label('Abrió'),

                TextColumn::make('initial_cash')
                    ->label('Fondo Inicial')
                    ->money('MXN'),

                TextColumn::make('total_sales')
                    ->label('Ventas')
                    ->getStateUsing(fn ($record) => $record->total_sales)
                    ->money('MXN'),

                TextColumn::make('expected_cash')
                    ->label('Efectivo Esperado')
                    ->getStateUsing(fn ($record) => $record->expected_cash)
                    ->money('MXN'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'open' => 'Abierto',
                        'closed' => 'Cerrado',
                    ])
                    ->query(function ($query, $state) {
                        return match ($state['value'] ?? null) {
                            'open' => $query->open(),
                            'closed' => $query->closed(),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->defaultSort('opened_at', 'desc');
    }
}
