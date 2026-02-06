<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Table extends Model
{
    public const ESTADO_DISPONIBLE = 'disponible';

    public const ESTADO_OCUPADA = 'ocupada';

    public const ESTADO_BLOQUEADA = 'bloqueada';

    protected $fillable = [
        'numero',
        'nombre',
        'capacidad',
        'estado',
        'qr_access',
        'orden_id',
        'ocupada_desde',
        'is_active',
        'orden_visual',
    ];

    protected $casts = [
        'qr_access' => 'boolean',
        'is_active' => 'boolean',
        'ocupada_desde' => 'datetime',
        'capacidad' => 'integer',
        'orden_visual' => 'integer',
    ];

    public static function getEstadoOptions(): array
    {
        return [
            self::ESTADO_DISPONIBLE => 'Disponible',
            self::ESTADO_OCUPADA => 'Ocupada',
            self::ESTADO_BLOQUEADA => 'Bloqueada',
        ];
    }

    public function orden(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'orden_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDisponibles($query)
    {
        return $query->where('estado', self::ESTADO_DISPONIBLE);
    }

    public function scopeOrdenadas($query)
    {
        return $query->orderBy('orden_visual')->orderBy('numero');
    }

    public function getQrUrlAttribute(): string
    {
        return url('/menu/'.$this->numero);
    }

    public function getTiempoOcupacionAttribute(): ?string
    {
        if (! $this->ocupada_desde) {
            return null;
        }

        $diff = now()->diff($this->ocupada_desde);

        if ($diff->h > 0) {
            return sprintf('%dh %dm', $diff->h, $diff->i);
        }

        return sprintf('%d min', $diff->i);
    }

    public function bloquear(): void
    {
        // Bloquear QR = no hay clientes = mesa disponible
        $this->update([
            'qr_access' => false,
            'estado' => self::ESTADO_DISPONIBLE,
            'ocupada_desde' => null,
        ]);
    }

    public function desbloquear(): void
    {
        // Desbloquear QR = hay clientes = mesa bloqueada (ocupada por clientes)
        $this->update([
            'qr_access' => true,
            'estado' => self::ESTADO_BLOQUEADA,
            'ocupada_desde' => now(),
        ]);
    }

    public function ocupar(Order $order): void
    {
        $this->update([
            'estado' => self::ESTADO_OCUPADA,
            'orden_id' => $order->id,
            'ocupada_desde' => now(),
            'qr_access' => true,
        ]);
    }

    public function liberar(): void
    {
        $this->update([
            'estado' => self::ESTADO_DISPONIBLE,
            'orden_id' => null,
            'ocupada_desde' => null,
            'qr_access' => false, // Bloqueado por defecto hasta que lleguen nuevos clientes
        ]);
    }
}
