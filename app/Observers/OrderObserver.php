<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Table;

class OrderObserver
{
    public function updated(Order $order)
    {
        // Si la orden se marca como entregada Y pagada, liberar la mesa
        if ($order->isDirty('status') || $order->isDirty('payment_status')) {
            if ($order->status === 'delivered' && $order->payment_status === 'paid') {
                $table = Table::where('orden_id', $order->id)->first();

                if ($table) {
                    $table->liberar();
                }
            }
        }
    }
}
