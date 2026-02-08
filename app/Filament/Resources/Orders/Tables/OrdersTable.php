<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Printer;
use App\Services\PrinterService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
                    ->getStateUsing(fn ($record) => $record->status)
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
                BadgeColumn::make('payment_status')
                    ->label('Pago')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'unpaid' => 'Sin pagar',
                        'partial' => 'Parcial',
                        'paid' => 'Pagado',
                        default => $state,
                    })
                    ->colors([
                        'danger' => 'unpaid',
                        'warning' => 'partial',
                        'success' => 'paid',
                    ]),
                TextColumn::make('total')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => '$'.number_format($state, 2))
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
                Action::make('reprint')
                    ->label('Reimprimir')
                    ->icon('heroicon-o-printer')
                    ->color('warning')
                    ->modalHeading('Reimprimir Ticket')
                    ->modalDescription('Selecciona el tipo de ticket a reimprimir. Se imprimirá en la impresora de caja.')
                    ->modalSubmitActionLabel('Imprimir')
                    ->schema([
                        Select::make('ticket_type')
                            ->label('Tipo de Ticket')
                            ->options([
                                Printer::STATION_COLD_BAR => 'Barra Fría (Cocina)',
                                Printer::STATION_HOT_BAR => 'Barra Caliente (Cocina)',
                                'receipt' => 'Ticket de Venta (Cuenta)',
                            ])
                            ->required(),
                    ])
                    ->action(function (array $data, $record): void {
                        $printerService = new PrinterService;
                        $result = $printerService->reprintToCashier($record, $data['ticket_type']);

                        if ($result) {
                            Notification::make()
                                ->title('Ticket reimpreso exitosamente')
                                ->success()
                                ->send();
                        } else {
                            $message = $data['ticket_type'] === 'receipt'
                                ? 'No se pudo reimprimir. Verifica que la impresora de caja esté activa.'
                                : 'No se pudo reimprimir. Verifica que la impresora esté activa o que la orden tenga items de esa estación.';
                            Notification::make()
                                ->title('Error al reimprimir')
                                ->body($message)
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
