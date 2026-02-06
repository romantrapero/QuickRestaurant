<?php

namespace App\Filament\Resources\Tables;

use App\Filament\Resources\Tables\Pages\CreateTable;
use App\Filament\Resources\Tables\Pages\EditTable;
use App\Filament\Resources\Tables\Pages\ListTables;
use App\Filament\Resources\Tables\Schemas\TableForm;
use App\Filament\Resources\Tables\Tables\TablesTable;
use App\Models\Table;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table as FilamentTable;
use UnitEnum;

class TableResource extends Resource
{
    protected static ?string $model = Table::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-s-squares-2x2';

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static ?string $modelLabel = 'Mesa';

    protected static string|UnitEnum|null $navigationGroup = 'Sistema';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return TableForm::configure($schema);
    }

    public static function table(FilamentTable $table): FilamentTable
    {
        return TablesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTables::route('/'),
            'create' => CreateTable::route('/create'),
            'edit' => EditTable::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return 'Mesas';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Mesas';
    }
}
