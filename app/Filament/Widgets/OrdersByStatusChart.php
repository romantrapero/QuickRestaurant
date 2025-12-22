<?php

namespace App\Filament\Widgets;

use Filament\Widgets\BarChartWidget;
use App\Models\Order;

class OrdersByStatusChart extends BarChartWidget
{
    protected ?string $heading = 'Órdenes por Estado';
    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $statuses = ['pending', 'preparing', 'ready', 'delivered', 'cancelled'];
        $counts = [];
        foreach ($statuses as $status) {
            $counts[] = Order::where('status', $status)->count();
        }
        return [
            'datasets' => [
                [
                    'label' => 'Órdenes',
                    'data' => $counts,
                    'backgroundColor' => [
                        '#fbbf24', '#3b82f6', '#10b981', '#22d3ee', '#ef4444'
                    ],
                ],
            ],
            'labels' => ['Pendiente', 'En preparación', 'Listo', 'Entregado', 'Cancelado'],
        ];
    }
}
