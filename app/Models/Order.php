<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'order_number',
        'status',
        'payment_status',
        'total',
        'table_number',
        'customer_name',
        'customer_notes',
        'completed_at',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-'.date('YmdHis').'-'.rand(100, 999);
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'preparing']);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function modifications(): HasMany
    {
        return $this->hasMany(OrderModification::class);
    }

    public function table(): HasOne
    {
        return $this->hasOne(Table::class, 'orden_id');
    }

    public function getTableInfoAttribute(): ?array
    {
        $table = $this->table;
        if (! $table) {
            return null;
        }

        return [
            'numero' => $table->numero,
            'nombre' => $table->nombre,
            'qr_access' => $table->qr_access,
            'tiempo_ocupacion' => $table->tiempo_ocupacion,
        ];
    }

    public function calculateTotal()
    {
        return $this->items->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });
    }

    public function recalculateTotal(): void
    {
        $this->total = $this->items()
            ->where('status', '!=', OrderItem::STATUS_CANCELLED)
            ->sum(\Illuminate\Support\Facades\DB::raw('quantity * unit_price'));
        $this->save();
        $this->updatePaymentStatus();
    }

    public function amountPaid(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function amountRemaining(): float
    {
        return max(0, (float) $this->total - $this->amountPaid());
    }

    public function updatePaymentStatus(): void
    {
        $paid = $this->amountPaid();

        if ($paid <= 0) {
            $this->payment_status = 'unpaid';
        } elseif ($paid >= (float) $this->total) {
            $this->payment_status = 'paid';
        } else {
            $this->payment_status = 'partial';
        }

        $this->saveQuietly();
    }
}
