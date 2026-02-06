<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RestaurantConfigResource\Pages;
use App\Filament\Resources\RestaurantConfigResource\Schemas\RestaurantConfigForm;
use App\Models\RestaurantConfig;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use UnitEnum;

class RestaurantConfigResource extends Resource
{
    protected static ?string $model = RestaurantConfig::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Configuración';

    protected static ?string $modelLabel = 'Configuración';

    protected static string|UnitEnum|null $navigationGroup = 'Sistema';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return RestaurantConfigForm::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageRestaurantConfig::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Solo un registro
    }

    public static function canDelete($record): bool
    {
        return false; // No se puede eliminar
    }
}
