<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;

class PaymentRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Pagos';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('method')
                ->label('Método')
                ->options([
                    'efectivo' => 'Efectivo',
                    'tarjeta' => 'Tarjeta',
                    'transferencia' => 'Transferencia',
                ])
                ->required(),
            TextInput::make('amount')
                ->label('Monto')
                ->numeric()
                ->prefix('$')
                ->required()
                ->minValue(0.01),
            TextInput::make('tip')
                ->label('Propina')
                ->numeric()
                ->prefix('$')
                ->default(0),
            TextInput::make('processed_by')
                ->label('Procesado por')
                ->default(fn () => auth()->user()?->name),
            Textarea::make('notes')
                ->label('Notas')
                ->columnSpanFull(),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                BadgeColumn::make('method')
                    ->label('Método')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'efectivo' => 'Efectivo',
                        'tarjeta' => 'Tarjeta',
                        'transferencia' => 'Transferencia',
                        default => $state,
                    })
                    ->colors([
                        'success' => 'efectivo',
                        'info' => 'tarjeta',
                        'warning' => 'transferencia',
                    ]),
                TextColumn::make('amount')->label('Monto')->money('MXN'),
                TextColumn::make('tip')->label('Propina')->money('MXN'),
                TextColumn::make('processed_by')->label('Procesado por'),
                TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Registrar Pago')
                    ->after(function ($record) {
                        $record->order->updatePaymentStatus();
                    }),
            ])
            ->actions([
                DeleteAction::make()
                    ->after(fn () => $this->getOwnerRecord()->updatePaymentStatus()),
            ]);
    }
}
