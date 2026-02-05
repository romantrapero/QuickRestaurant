<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderModification extends Model
{
    protected $fillable = [
        'order_id',
        'order_item_id',
        'type',
        'dish_name',
        'quantity',
        'unit_price',
        'total_before',
        'total_after',
        'modified_by',
        'reason',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_before' => 'decimal:2',
        'total_after' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
