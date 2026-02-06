<?php

namespace App\Filament\Resources\Tables\Tables;

use App\Models\Table;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table as FilamentTable;

class TablesTable
{
    public static function configure(FilamentTable $table): FilamentTable
    {
        return $table
            ->columns([
                TextColumn::make('numero')
                    ->label('Número')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Sin nombre'),
                TextColumn::make('capacidad')
                    ->label('Capacidad')
                    ->numeric()
                    ->sortable()
                    ->suffix(' pers.'),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Table::ESTADO_DISPONIBLE => 'success',
                        Table::ESTADO_OCUPADA => 'warning',
                        Table::ESTADO_BLOQUEADA => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => Table::getEstadoOptions()[$state] ?? $state),
                ToggleColumn::make('qr_access')
                    ->label('QR Activo')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Activa')
                    ->sortable(),
                TextColumn::make('orden_visual')
                    ->label('Orden')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('orden.order_number')
                    ->label('Orden Actual')
                    ->placeholder('-')
                    ->badge()
                    ->color('info'),
                TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(Table::getEstadoOptions()),
                TernaryFilter::make('qr_access')
                    ->label('Acceso QR'),
                TernaryFilter::make('is_active')
                    ->label('Activa'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('orden_visual', 'asc');
    }
}
