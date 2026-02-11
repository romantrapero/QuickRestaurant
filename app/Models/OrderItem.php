<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class OrderItem extends Model
{
    /** @use HasFactory<\Database\Factories\OrderItemFactory> */
    use HasFactory;

    // Constantes de estado
    public const STATUS_PENDING = 'pending';

    public const STATUS_SENT_TO_KITCHEN = 'sent_to_kitchen';

    public const STATUS_PREPARING = 'preparing';

    public const STATUS_READY = 'ready';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'order_id',
        'dish_id',
        'quantity',
        'unit_price',
        'total_price',
        'special_instructions',
        'printed_at',
        'status',
        'print_count',
    ];

    protected function casts(): array
    {
        return [
            'printed_at' => 'datetime',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
            'print_count' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function dish(): BelongsTo
    {
        return $this->belongsTo(Dish::class);
    }

    // Scopes
    public function scopeNotPrinted($query)
    {
        return $query->whereNull('printed_at');
    }

    public function scopePendingPrint($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING]);
    }

    public function scopeModifiedSinceLastPrint($query)
    {
        return $query->whereNotNull('printed_at')
            ->where('updated_at', '>', DB::raw('printed_at'));
    }

    // Métodos
    public function markAsPrinted(): void
    {
        $updateData = [
            'printed_at' => now(),
            'print_count' => $this->print_count + 1,
        ];

        // Solo cambiar status si NO está cancelado
        if ($this->status !== self::STATUS_CANCELLED) {
            $updateData['status'] = self::STATUS_SENT_TO_KITCHEN;
        }

        $this->update($updateData);
    }

    public function needsReprint(): bool
    {
        if (is_null($this->printed_at)) {
            return true; // Nunca impreso
        }

        if ($this->status === self::STATUS_CANCELLED) {
            return false; // No reimprimir cancelados
        }

        // Reimprimir si se modificó después de imprimir
        return $this->updated_at > $this->printed_at;
    }
}
