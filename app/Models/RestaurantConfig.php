<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantConfig extends Model
{
    protected $fillable = [
        'nombre_comercial',
        'telefono',
        'email',
        'direccion',
        'numero_mesas',
        'iva_porcentaje',
        'horario_apertura',
        'horario_cierre',
        'dias_operacion',
        'moneda',
        'timezone',
    ];

    protected $casts = [
        'numero_mesas' => 'integer',
        'iva_porcentaje' => 'decimal:2',
        'dias_operacion' => 'array',
    ];

    public static function config(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'nombre_comercial' => 'QuickRestaurant',
                'numero_mesas' => 10,
                'iva_porcentaje' => 16.00,
                'moneda' => 'MXN',
                'timezone' => 'America/Mexico_City',
            ]
        );
    }

    public function getDiasOperacionTextAttribute(): string
    {
        if (! $this->dias_operacion) {
            return 'Todos los días';
        }

        $dias = [
            0 => 'Domingo',
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
        ];

        return collect($this->dias_operacion)
            ->map(fn ($d) => $dias[$d] ?? '')
            ->implode(', ');
    }

    public function getIvaTasaAttribute(): float
    {
        return $this->iva_porcentaje / 100;
    }
}
