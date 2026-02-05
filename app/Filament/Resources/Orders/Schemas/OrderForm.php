<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Support\Colors\Color;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->label('Número de Orden')
                    ->required(),
                TextInput::make('customer_name')
                    ->label('Nombre del Cliente'),
                TextInput::make('total')
                    ->label('Total')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->mask(fn ($state) => number_format((float) $state, 2, '.', ',')),
                Select::make('table_number')
                    ->label('Tipo de Orden')
                    ->options([
                        'To Go' => 'To Go',
                        'Didi Food' => 'Didi Food',
                        'Uber Eats' => 'Uber Eats',
                        'Rappi' => 'Rappi',
                        'Otro' => 'Otro',
                    ])
                    ->required(),
                Select::make('payment_status')
                    ->label('Estado de Pago')
                    ->options([
                        'unpaid' => 'Sin pagar',
                        'partial' => 'Parcial',
                        'paid' => 'Pagado',
                    ])
                    ->disabled(),
                Textarea::make('customer_notes')
                    ->label('Notas del Cliente')
                    ->columnSpanFull(),
            ]);
    }
}
