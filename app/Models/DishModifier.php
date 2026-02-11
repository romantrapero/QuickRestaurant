<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DishModifier extends Model
{
    public const TYPE_EXTRA = 'extra';

    public const TYPE_EXCEPTION = 'exception';

    protected $fillable = [
        'dish_id',
        'name',
        'type',
        'price',
        'is_available',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_available' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    // Relaciones
    public function dish(): BelongsTo
    {
        return $this->belongsTo(Dish::class);
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeExtras($query)
    {
        return $query->where('type', self::TYPE_EXTRA);
    }

    public function scopeExceptions($query)
    {
        return $query->where('type', self::TYPE_EXCEPTION);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    // Métodos
    public function isExtra(): bool
    {
        return $this->type === self::TYPE_EXTRA;
    }

    public function isException(): bool
    {
        return $this->type === self::TYPE_EXCEPTION;
    }

    public function isFree(): bool
    {
        return $this->price == 0;
    }
}
