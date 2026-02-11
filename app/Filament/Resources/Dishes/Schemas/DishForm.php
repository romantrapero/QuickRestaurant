<?php

namespace App\Filament\Resources\Dishes\Schemas;

use App\Models\Category;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DishForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->label('Categoría')
                    ->required()
                    ->options(Category::pluck('name', 'id'))
                    ->searchable(),
                TextInput::make('name')
                    ->label('Nombre')
                    ->required(),
                Textarea::make('description')
                    ->label('Descripción')
                    ->columnSpanFull(),
                TextInput::make('cost_price')
                    ->label('Costo')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('sale_price')
                    ->label('Precio de venta')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Toggle::make('is_available')
                    ->label('Activo')
                    ->required()
                    ->default(true),
                FileUpload::make('image_url')
                    // ->disk('public')
                    ->image()
                    ->label('Imagen'),
                TextInput::make('preparation_time')
                    ->label('Tiempo de preparación')
                    ->required()
                    ->numeric()
                    ->default(15),
            ]);
    }
}
