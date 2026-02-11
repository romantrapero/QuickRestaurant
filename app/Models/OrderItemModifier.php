<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemModifier extends Model
{
    protected $fillable = [
        'order_item_id',
        'dish_modifier_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    // Relaciones
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function dishModifier(): BelongsTo
    {
        return $this->belongsTo(DishModifier::class);
    }

    // Observers/Events
    protected static function booted(): void
    {
        static::creating(function (self $modifier) {
            // Calcular total automáticamente
            $modifier->total_price = $modifier->quantity * $modifier->unit_price;
        });

        static::updating(function (self $modifier) {
            // Recalcular si cambia cantidad
            if ($modifier->isDirty('quantity') || $modifier->isDirty('unit_price')) {
                $modifier->total_price = $modifier->quantity * $modifier->unit_price;
            }
        });
    }
}
