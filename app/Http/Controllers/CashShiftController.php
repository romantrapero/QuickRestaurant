<?php

namespace App\Http\Controllers;

use App\Models\CashShift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashShiftController extends Controller
{
    /**
     * Get current open shift status
     */
    public function current()
    {
        $shift = CashShift::current();

        if (! $shift) {
            return response()->json([
                'is_open' => false,
                'shift' => null,
            ]);
        }

        return response()->json([
            'is_open' => true,
            'shift' => [
                'id' => $shift->id,
                'opened_by' => $shift->opened_by,
                'opened_at' => $shift->opened_at->format('H:i'),
                'initial_cash' => $shift->initial_cash,
                'duration' => $shift->duration,
                'total_sales' => $shift->total_sales,
                'orders_count' => $shift->orders_count,
            ],
        ]);
    }

    /**
     * Open a new shift
     */
    public function open(Request $request)
    {
        if (CashShift::isShiftOpen()) {
            return response()->json([
                'success' => false,
                'message' => 'Ya hay un turno abierto',
            ], 400);
        }

        $validated = $request->validate([
            'initial_cash' => 'required|numeric|min:0',
        ]);

        $shift = CashShift::create([
            'opened_by' => Auth::user()?->name ?? 'POS',
            'initial_cash' => $validated['initial_cash'],
            'opened_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Turno abierto exitosamente',
            'shift' => [
                'id' => $shift->id,
                'opened_by' => $shift->opened_by,
                'opened_at' => $shift->opened_at->format('H:i'),
                'initial_cash' => $shift->initial_cash,
            ],
        ]);
    }

    /**
     * Close current shift
     */
    public function close(Request $request)
    {
        $shift = CashShift::current();

        if (! $shift) {
            return response()->json([
                'success' => false,
                'message' => 'No hay turno abierto',
            ], 400);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $shift->update([
            'closed_by' => Auth::user()?->name ?? 'POS',
            'closed_at' => now(),
            'notes' => $validated['notes'] ?? null,
        ]);

        $shift->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Turno cerrado exitosamente',
            'summary' => [
                'id' => $shift->id,
                'opened_at' => $shift->opened_at->format('d/m/Y H:i'),
                'closed_at' => $shift->closed_at->format('d/m/Y H:i'),
                'duration' => $shift->duration,
                'opened_by' => $shift->opened_by,
                'closed_by' => $shift->closed_by,
                'initial_cash' => $shift->initial_cash,
                'sales_by_method' => $shift->sales_by_method,
                'total_sales' => $shift->total_sales,
                'total_tips' => $shift->total_tips,
                'expected_cash' => $shift->expected_cash,
                'orders_count' => $shift->orders_count,
            ],
        ]);
    }
}
