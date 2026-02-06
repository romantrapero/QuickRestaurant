<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\PieChartWidget;

class PaymentMethodsChart extends PieChartWidget
{
    protected ?string $heading = 'Métodos de Pago';

    protected ?string $maxHeight = '300px';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $efectivo = Payment::where('method', 'efectivo')->sum('amount');
        $tarjeta = Payment::where('method', 'tarjeta')->sum('amount');
        $transferencia = Payment::where('method', 'transferencia')->sum('amount');

        return [
            'datasets' => [
                [
                    'label' => 'Monto',
                    'data' => [$efectivo, $tarjeta, $transferencia],
                    'backgroundColor' => [
                        '#10b981', // Verde - Efectivo
                        '#3b82f6', // Azul - Tarjeta
                        '#f59e0b', // Amarillo - Transferencia
                    ],
                ],
            ],
            'labels' => ['Efectivo', 'Tarjeta', 'Transferencia'],
        ];
    }
}
