<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\LineChartWidget;

class WeeklySalesChart extends LineChartWidget
{
    protected ?string $heading = 'Ventas de la Semana';

    protected ?string $maxHeight = '300px';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $days = collect();
        $sales = collect();

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $days->push($date->locale('es')->isoFormat('ddd'));

            $daySales = Payment::whereDate('created_at', $date)->sum('amount');
            $sales->push($daySales);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Ventas',
                    'data' => $sales->toArray(),
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $days->toArray(),
        ];
    }
}
