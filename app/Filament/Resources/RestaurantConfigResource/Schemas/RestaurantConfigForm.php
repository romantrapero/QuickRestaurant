<?php

namespace App\Filament\Resources\RestaurantConfigResource\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RestaurantConfigForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Información General')
                    ->description('Datos básicos del restaurante')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nombre_comercial')
                            ->label('Nombre Comercial')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('QuickRestaurant'),

                        TextInput::make('telefono')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('555-123-4567'),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255)
                            ->placeholder('contacto@restaurante.com'),

                        Textarea::make('direccion')
                            ->label('Dirección')
                            ->maxLength(500)
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Configuración Operativa')
                    ->description('Impuestos y horarios')
                    ->columns(2)
                    ->schema([
                        TextInput::make('iva_porcentaje')
                            ->label('IVA / Impuesto (%)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->suffix('%')
                            ->default(16.00),

                        TimePicker::make('horario_apertura')
                            ->label('Hora de Apertura')
                            ->seconds(false)
                            ->format('H:i'),

                        TimePicker::make('horario_cierre')
                            ->label('Hora de Cierre')
                            ->seconds(false)
                            ->format('H:i'),

                        Select::make('dias_operacion')
                            ->label('Días de Operación')
                            ->multiple()
                            ->options([
                                1 => 'Lunes',
                                2 => 'Martes',
                                3 => 'Miércoles',
                                4 => 'Jueves',
                                5 => 'Viernes',
                                6 => 'Sábado',
                                0 => 'Domingo',
                            ])
                            ->helperText('Deja vacío para operar todos los días')
                            ->columnSpanFull(),

                        Select::make('moneda')
                            ->label('Moneda')
                            ->options([
                                'MXN' => 'Peso Mexicano (MXN)',
                                'USD' => 'Dólar Americano (USD)',
                                'EUR' => 'Euro (EUR)',
                            ])
                            ->default('MXN')
                            ->required(),

                        Select::make('timezone')
                            ->label('Zona Horaria')
                            ->options([
                                'America/Mexico_City' => 'Ciudad de México (GMT-6)',
                                'America/Tijuana' => 'Tijuana (GMT-8)',
                                'America/Cancun' => 'Cancún (GMT-5)',
                            ])
                            ->default('America/Mexico_City')
                            ->required(),
                    ]),
            ]);
    }
}
