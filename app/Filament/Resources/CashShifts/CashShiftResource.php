<?php

namespace App\Filament\Resources\CashShifts;

use App\Filament\Resources\CashShifts\Pages\ListCashShifts;
use App\Filament\Resources\CashShifts\Pages\ViewCashShift;
use App\Filament\Resources\CashShifts\Tables\CashShiftsTable;
use App\Models\CashShift;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use UnitEnum;

class CashShiftResource extends Resource
{
    protected static ?string $model = CashShift::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationLabel = 'Cortes de Caja';

    protected static ?string $modelLabel = 'Corte de Caja';

    protected static ?string $pluralModelLabel = 'Cortes de Caja';

    protected static string|UnitEnum|null $navigationGroup = 'Operaciones';

    protected static ?int $navigationSort = 4;

    public static function table(Table $table): Table
    {
        return CashShiftsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCashShifts::route('/'),
            'view' => ViewCashShift::route('/{record}'),
        ];
    }
}
