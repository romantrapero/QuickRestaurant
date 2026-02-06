<?php

namespace App\Filament\Resources\Dishes;

use App\Filament\Resources\Dishes\Pages\CreateDish;
use App\Filament\Resources\Dishes\Pages\EditDish;
use App\Filament\Resources\Dishes\Pages\ListDishes;
use App\Filament\Resources\Dishes\Schemas\DishForm;
use App\Filament\Resources\Dishes\Tables\DishesTable;
use App\Models\Dish;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class DishResource extends Resource
{
    protected static ?string $model = Dish::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-s-list-bullet';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Platillo';

    protected static string|UnitEnum|null $navigationGroup = 'Menú';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return DishForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DishesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDishes::route('/'),
            'create' => CreateDish::route('/create'),
            'edit' => EditDish::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return 'Platillos';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Platillos';
    }
}
