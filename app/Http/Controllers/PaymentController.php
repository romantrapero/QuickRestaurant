<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\PrinterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function store(Request $request, Order $order)
    {
        $validated = $request->validate([
            'method' => 'required|in:efectivo,tarjeta,transferencia',
            'amount' => 'required|numeric|min:0.01',
            'tip' => 'nullable|numeric|min:0',
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'method' => $validated['method'],
            'amount' => $validated['amount'],
            'tip' => $validated['tip'] ?? 0,
            'processed_by' => Auth::user()?->name ?? 'POS',
        ]);

        $order->updatePaymentStatus();
        $order->refresh();

        // Imprimir recibo si el pago está completo
        $receiptPrinted = false;
        if ($order->payment_status === 'paid') {
            try {
                $printerService = new PrinterService;
                $receiptPrinted = $printerService->printReceipt($order);
            } catch (\Exception $e) {
                Log::error("Error al imprimir recibo {$order->order_number}: ".$e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'payment' => $payment,
            'order' => [
                'id' => $order->id,
                'payment_status' => $order->payment_status,
                'amount_paid' => $order->amountPaid(),
                'amount_remaining' => $order->amountRemaining(),
            ],
            'receipt_printed' => $receiptPrinted,
        ]);
    }
}
