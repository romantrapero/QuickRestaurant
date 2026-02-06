<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentPayments extends BaseWidget
{
    protected static ?string $heading = 'Últimas Transacciones';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(
                Payment::query()
                    ->with('order')
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Hora')
                    ->dateTime('H:i')
                    ->sortable(false),
                Tables\Columns\TextColumn::make('order.customer_name')
                    ->label('Cliente')
                    ->placeholder('Sin nombre')
                    ->limit(15),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->money('MXN'),
                Tables\Columns\TextColumn::make('method')
                    ->label('Método')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'efectivo' => 'Efectivo',
                        'tarjeta' => 'Tarjeta',
                        'transferencia' => 'Transf.',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'efectivo' => 'success',
                        'tarjeta' => 'info',
                        'transferencia' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->paginated(false);
    }
}
