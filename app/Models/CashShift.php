<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashShift extends Model
{
    protected $fillable = [
        'opened_by',
        'closed_by',
        'initial_cash',
        'opened_at',
        'closed_at',
        'notes',
    ];

    protected $casts = [
        'initial_cash' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    // ===== Scopes =====

    public function scopeOpen($query)
    {
        return $query->whereNull('closed_at');
    }

    public function scopeClosed($query)
    {
        return $query->whereNotNull('closed_at');
    }

    // ===== Static Helpers =====

    public static function current(): ?self
    {
        return self::open()->latest('opened_at')->first();
    }

    public static function isShiftOpen(): bool
    {
        return self::open()->exists();
    }

    // ===== Calculated Attributes =====

    /**
     * Get payments made during this shift's timeframe
     */
    public function getPaymentsQuery()
    {
        return Payment::whereBetween('created_at', [
            $this->opened_at,
            $this->closed_at ?? now(),
        ]);
    }

    /**
     * Get total sales by payment method
     */
    public function getSalesByMethodAttribute(): array
    {
        return $this->getPaymentsQuery()
            ->selectRaw('method, SUM(amount) as total')
            ->groupBy('method')
            ->pluck('total', 'method')
            ->toArray();
    }

    /**
     * Get total tips collected during shift
     */
    public function getTotalTipsAttribute(): float
    {
        return (float) $this->getPaymentsQuery()->sum('tip');
    }

    /**
     * Get total sales (all methods)
     */
    public function getTotalSalesAttribute(): float
    {
        return (float) $this->getPaymentsQuery()->sum('amount');
    }

    /**
     * Get cash sales specifically
     */
    public function getCashSalesAttribute(): float
    {
        return (float) $this->getPaymentsQuery()
            ->where('method', 'efectivo')
            ->sum('amount');
    }

    /**
     * Calculate expected cash in drawer
     * = initial_cash + cash_sales + tips (assuming tips are in cash)
     */
    public function getExpectedCashAttribute(): float
    {
        return (float) $this->initial_cash + $this->cash_sales + $this->total_tips;
    }

    /**
     * Get count of orders during shift
     */
    public function getOrdersCountAttribute(): int
    {
        return $this->getPaymentsQuery()
            ->distinct('order_id')
            ->count('order_id');
    }

    /**
     * Duration of shift in human readable format
     */
    public function getDurationAttribute(): string
    {
        $end = $this->closed_at ?? now();

        return $this->opened_at->diffForHumans($end, true);
    }

    /**
     * Check if shift is currently open
     */
    public function getIsOpenAttribute(): bool
    {
        return $this->closed_at === null;
    }
}
