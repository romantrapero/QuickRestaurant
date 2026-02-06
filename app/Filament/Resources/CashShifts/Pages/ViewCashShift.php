<?php

namespace App\Filament\Resources\CashShifts\Pages;

use App\Filament\Resources\CashShifts\CashShiftResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewCashShift extends ViewRecord
{
    protected static string $resource = CashShiftResource::class;

    protected static ?string $title = 'Detalle de Corte de Caja';

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Turno')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('opened_at')
                                ->label('Apertura')
                                ->dateTime('d/m/Y H:i'),
                            TextEntry::make('closed_at')
                                ->label('Cierre')
                                ->dateTime('d/m/Y H:i')
                                ->placeholder('En curso'),
                            TextEntry::make('duration')
                                ->label('Duración'),
                        ]),
                        Grid::make(2)->schema([
                            TextEntry::make('opened_by')
                                ->label('Abrió'),
                            TextEntry::make('closed_by')
                                ->label('Cerró')
                                ->placeholder('-'),
                        ]),
                    ]),

                Section::make('Resumen de Caja')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('initial_cash')
                                ->label('Fondo Inicial')
                                ->money('MXN'),
                            TextEntry::make('orders_count')
                                ->label('Órdenes Cobradas'),
                        ]),
                    ]),

                Section::make('Ventas por Método de Pago')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('efectivo')
                                ->label('Efectivo')
                                ->state(fn ($record) => $record->sales_by_method['efectivo'] ?? 0)
                                ->money('MXN'),
                            TextEntry::make('tarjeta')
                                ->label('Tarjeta')
                                ->state(fn ($record) => $record->sales_by_method['tarjeta'] ?? 0)
                                ->money('MXN'),
                            TextEntry::make('transferencia')
                                ->label('Transferencia')
                                ->state(fn ($record) => $record->sales_by_method['transferencia'] ?? 0)
                                ->money('MXN'),
                        ]),
                    ]),

                Section::make('Totales')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('total_sales')
                                ->label('Total Ventas')
                                ->money('MXN'),
                            TextEntry::make('total_tips')
                                ->label('Total Propinas')
                                ->money('MXN'),
                            TextEntry::make('expected_cash')
                                ->label('Efectivo Esperado en Caja')
                                ->money('MXN')
                                ->helperText('Fondo inicial + ventas en efectivo + propinas'),
                        ]),
                    ]),

                Section::make('Notas')
                    ->schema([
                        TextEntry::make('notes')
                            ->label('')
                            ->placeholder('Sin notas'),
                    ])
                    ->collapsed(),
            ]);
    }
}
