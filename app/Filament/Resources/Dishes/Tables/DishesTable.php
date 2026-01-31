<?php

namespace App\Filament\Resources\Dishes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Columns\ToggleColumn;

class DishesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nombre del Plato')
                    ->searchable(),
                TextColumn::make('cost_price')
                    ->label('Costo')
                    ->formatStateUsing(fn($state) => '$' . number_format($state, 2))
                    ->sortable(),
                TextColumn::make('sale_price')
                    ->label('Precio de Venta')
                    ->formatStateUsing(fn($state) => '$' . number_format($state, 2))
                    ->sortable(),
                ToggleColumn::make('is_available')
                    ->label('Activo')
                    ->sortable(),
                ImageColumn::make('image_url')
                    //->disk('public')
                    ->label('Imagen')
                    ->rounded(),
                TextColumn::make('preparation_time')
                    ->label('Preparación (min)')
                    ->numeric()
                    ->sortable(),
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
            ]);
    }
}
