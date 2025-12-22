<?php

namespace App\Filament\Widgets;

use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use App\Models\Order;

class RecentOrdersTable extends BaseWidget
{
    protected static ?string $heading = 'Órdenes recientes';
    protected static ?int $recordsPerPage = 5;

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(Order::query()->latest('created_at')->limit(10))
            ->columns([
                Tables\Columns\TextColumn::make('order_number')->label('N° Orden'),
                Tables\Columns\TextColumn::make('customer_name')->label('Cliente'),
                Tables\Columns\TextColumn::make('table_number')->label('Mesa'),
                Tables\Columns\TextColumn::make('status')->label('Estado'),
                Tables\Columns\TextColumn::make('total')->label('Total')->money('MXN'),
                Tables\Columns\TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i'),
            ]);
    }
}
