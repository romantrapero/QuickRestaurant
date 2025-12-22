<?php

namespace App\Filament\Widgets;

use Filament\Widgets\PieChartWidget;
use App\Models\Dish;

class DishesByCategoryChart extends PieChartWidget
{
    protected ?string $heading = 'Platillos por Categoría';
    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $categories = \App\Models\Category::pluck('name', 'id');
        $data = [];
        foreach ($categories as $id => $name) {
            $data[] = Dish::where('category_id', $id)->count();
        }
        // Paleta de colores (agrega más si tienes más categorías)
        $colors = [
            '#fbbf24', // Amarillo
            '#3b82f6', // Azul
            '#10b981', // Verde
            '#ef4444', // Rojo
            '#a78bfa', // Morado
            '#f472b6', // Rosa
            '#f87171', // Rojo claro
            '#34d399', // Verde claro
            '#60a5fa', // Azul claro
            '#facc15', // Amarillo claro
        ];
        // Ajusta la cantidad de colores a la cantidad de categorías
        $backgroundColors = array_slice($colors, 0, count($categories));
        return [
            'datasets' => [
                [
                    'label' => 'Platos',
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                ],
            ],
            'labels' => $categories->values()->toArray(),
        ];
    }
}
