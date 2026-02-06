<?php

namespace App\Filament\Resources\Tables\Schemas;

use App\Models\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TableForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la Mesa')
                    ->columns(2)
                    ->schema([
                        TextInput::make('numero')
                            ->label('Número/Identificador')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->helperText('Identificador único para el QR (ej: 1, A1, terraza-1)'),
                        TextInput::make('nombre')
                            ->label('Nombre')
                            ->maxLength(100)
                            ->placeholder('Mesa 1, Terraza A, VIP')
                            ->helperText('Nombre descriptivo opcional'),
                        TextInput::make('capacidad')
                            ->label('Capacidad')
                            ->required()
                            ->numeric()
                            ->default(4)
                            ->minValue(1)
                            ->maxValue(20)
                            ->suffix('personas'),
                        TextInput::make('orden_visual')
                            ->label('Orden Visual')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->helperText('Posición en el gestor de mesas'),
                    ]),
                Section::make('Estado y Acceso')
                    ->columns(2)
                    ->schema([
                        Select::make('estado')
                            ->label('Estado')
                            ->options(Table::getEstadoOptions())
                            ->default(Table::ESTADO_DISPONIBLE)
                            ->required(),
                        Toggle::make('qr_access')
                            ->label('Acceso QR Habilitado')
                            ->helperText('Si está desactivado, clientes solo pueden ver el menú sin ordenar')
                            ->default(false),
                        Toggle::make('is_active')
                            ->label('Mesa Activa')
                            ->helperText('Mesas inactivas no aparecen en el gestor')
                            ->default(true),
                    ]),
            ]);
    }
}
