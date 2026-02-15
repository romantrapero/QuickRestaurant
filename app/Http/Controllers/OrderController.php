<?php

namespace App\Http\Controllers;

use App\Models\CashShift;
use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderModification;
use App\Models\Table;
use App\Services\PrinterService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Order::with('items.dish')
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        return response()->json([
            'success' => true,
            'orders' => $orders,
            'count' => $orders->count(),
        ]);
    }

    /**
     * Get active orders (pending payment)
     */
    public function active()
    {
        $orders = Order::with(['items' => function ($query) {
            $query->where('status', '!=', OrderItem::STATUS_CANCELLED);
        }, 'items.dish', 'payments'])
            ->whereIn('status', ['pending', 'preparing', 'ready'])
            ->where('payment_status', '!=', 'paid')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'total' => $order->total,
                    'table_number' => $order->table_number,
                    'customer_name' => $order->customer_name,
                    'amount_paid' => $order->amountPaid(),
                    'amount_remaining' => $order->amountRemaining(),
                    'created_at' => $order->created_at,
                    'items' => $order->items,
                ];
            });

        return response()->json([
            'success' => true,
            'orders' => $orders,
            'count' => $orders->count(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Verificar que haya turno abierto
        if (! CashShift::isShiftOpen()) {
            return response()->json([
                'success' => false,
                'message' => 'No hay turno abierto. Abre un turno de caja antes de crear órdenes.',
            ], 422);
        }

        // Validar datos de entrada
        $validated = $request->validate([
            'table' => 'required|string|max:255',
            'customer' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:dishes,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.special_instructions' => 'nullable|string|max:500',
        ]);

        // Usar transacción para asegurar integridad, con retry por si hay race condition
        $attempts = 0;
        retry:
        $attempts++;

        try {
            return DB::transaction(function () use ($validated) {
                // Crear la orden
                $order = Order::create([
                    'order_number' => $this->generateOrderNumber(),
                    'status' => 'pending',
                    'table_number' => $validated['table'],
                    'customer_name' => $validated['customer'] ?? 'Cliente no identificado',
                    'customer_notes' => $validated['notes'] ?? null,
                    'total' => 0, // Se calculará después
                ]);

                $total = 0;

                // Crear los items de la orden
                foreach ($validated['items'] as $itemData) {
                    $dish = Dish::find($itemData['id']);

                    $orderItem = OrderItem::create([
                        'order_id' => $order->id,
                        'dish_id' => $itemData['id'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['price'],
                        'total_price' => $itemData['price'] * $itemData['quantity'],
                        'special_instructions' => $itemData['special_instructions'] ?? null,
                    ]);

                    $total += $orderItem->total_price;
                }

                // Actualizar el total de la orden
                $order->update(['total' => $total]);

                // Si viene de una mesa, ocuparla
                if ($validated['table']) {
                    $table = Table::where('numero', $validated['table'])->first();
                    if ($table && $table->estado === Table::ESTADO_DISPONIBLE) {
                        $table->ocupar($order);
                    }
                }

                // Imprimir tickets a las estaciones correspondientes
                $printResults = [];
                try {
                    $printerService = new PrinterService;
                    $printResults = $printerService->printOrder($order);
                } catch (\Exception $e) {
                    Log::error("Error al imprimir orden {$order->order_number}: ".$e->getMessage());
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Orden creada exitosamente',
                    'order' => [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'total' => $order->total,
                        'status' => $order->status,
                        'created_at' => $order->created_at->format('d/m/Y H:i'),
                    ],
                    'items_count' => $order->items()->count(),
                    'print_results' => $printResults,
                ], 201);
            });
        } catch (UniqueConstraintViolationException $e) {
            if ($attempts < 3) {
                goto retry;
            }
            throw $e;
        }
    }

    private function generateOrderNumber(): string
    {
        $date = date('Ymd');
        $prefix = 'ORD-'.$date.'-';

        $maxSeq = Order::where('order_number', 'like', $prefix.'%')
            ->selectRaw("MAX(CAST(SUBSTRING(order_number FROM '.{4}$') AS INTEGER)) as max_seq")
            ->value('max_seq') ?? 0;

        return $prefix.str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        // Cargar items excluyendo cancelados
        $order->load(['items' => function ($query) {
            $query->where('status', '!=', OrderItem::STATUS_CANCELLED);
        }, 'items.dish']);

        // An order is "closed" when it's delivered AND fully paid
        $isClosed = $order->status === 'delivered' && $order->payment_status === 'paid';

        return response()->json([
            'success' => true,
            'order' => $order,
            'items' => $order->items,
            'is_closed' => $isClosed,
        ]);
    }

    public function updateItem(Request $request, Order $order, OrderItem $orderItem): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        if (in_array($order->status, ['delivered', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'No se pueden modificar items de una orden entregada o cancelada.',
            ], 422);
        }

        if ($orderItem->order_id !== $order->id) {
            return response()->json([
                'success' => false,
                'message' => 'El item no pertenece a esta orden.',
            ], 422);
        }

        return DB::transaction(function () use ($order, $orderItem, $validated) {
            $totalBefore = (float) $order->total;
            $dish = $orderItem->dish;

            $orderItem->update([
                'quantity' => $validated['quantity'],
                'total_price' => $orderItem->unit_price * $validated['quantity'],
            ]);

            $order->recalculateTotal();

            OrderModification::create([
                'order_id' => $order->id,
                'order_item_id' => $orderItem->id,
                'type' => 'quantity_changed',
                'dish_name' => $dish->name,
                'quantity' => $validated['quantity'],
                'unit_price' => $orderItem->unit_price,
                'total_before' => $totalBefore,
                'total_after' => $order->total,
                'modified_by' => 'POS',
            ]);

            // Imprimir ticket de modificación
            $printResults = [];
            try {
                $printerService = new PrinterService;
                $printResults = $printerService->printOrder($order);
            } catch (\Exception $e) {
                Log::error("Error al imprimir modificación de item {$order->order_number}: ".$e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Cantidad actualizada',
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total' => $order->total,
                ],
                'print_results' => $printResults,
            ]);
        });
    }

    public function removeItem(Request $request, Order $order, OrderItem $orderItem): \Illuminate\Http\JsonResponse
    {
        if (in_array($order->status, ['delivered', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'No se pueden modificar items de una orden entregada o cancelada.',
            ], 422);
        }

        if ($orderItem->order_id !== $order->id) {
            return response()->json([
                'success' => false,
                'message' => 'El item no pertenece a esta orden.',
            ], 422);
        }

        return DB::transaction(function () use ($order, $orderItem, $request) {
            $totalBefore = (float) $order->total;
            $dish = $orderItem->dish;

            // Marcar como cancelado en lugar de borrar
            $orderItem->update([
                'status' => OrderItem::STATUS_CANCELLED,
            ]);

            OrderModification::create([
                'order_id' => $order->id,
                'order_item_id' => $orderItem->id,
                'type' => 'item_removed',
                'dish_name' => $dish->name,
                'quantity' => $orderItem->quantity,
                'unit_price' => $orderItem->unit_price,
                'total_before' => $totalBefore,
                'total_after' => $totalBefore - $orderItem->total_price,
                'modified_by' => 'POS',
                'reason' => $request->input('reason'),
            ]);

            $order->recalculateTotal();

            // Imprimir ticket de cancelación
            $printResults = [];
            try {
                $printerService = new PrinterService;
                $printResults = $printerService->printOrder($order);
            } catch (\Exception $e) {
                Log::error("Error al imprimir cancelación de item {$order->order_number}: ".$e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Item eliminado exitosamente',
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total' => $order->total,
                ],
                'print_results' => $printResults,
            ]);
        });
    }

    public function addItems(Request $request, Order $order)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:dishes,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.special_instructions' => 'nullable|string|max:500',
        ]);

        if (in_array($order->status, ['delivered', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'No se pueden agregar items a una orden entregada o cancelada.',
            ], 422);
        }

        return DB::transaction(function () use ($order, $validated) {
            $totalBefore = (float) $order->total;

            foreach ($validated['items'] as $itemData) {
                $dish = Dish::find($itemData['id']);

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'dish_id' => $itemData['id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['price'],
                    'total_price' => $itemData['price'] * $itemData['quantity'],
                    'special_instructions' => $itemData['special_instructions'] ?? null,
                ]);

                $order->recalculateTotal();

                OrderModification::create([
                    'order_id' => $order->id,
                    'order_item_id' => $orderItem->id,
                    'type' => 'item_added',
                    'dish_name' => $dish->name,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['price'],
                    'total_before' => $totalBefore,
                    'total_after' => $order->total,
                    'modified_by' => 'QR Menu',
                ]);

                $totalBefore = (float) $order->total;
            }

            $order->refresh();

            // Imprimir los nuevos items
            $printResults = [];
            try {
                $printerService = new PrinterService;
                $printResults = $printerService->printOrder($order);
            } catch (\Exception $e) {
                Log::error("Error al imprimir items adicionales {$order->order_number}: ".$e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Items agregados exitosamente',
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total' => $order->total,
                    'status' => $order->status,
                ],
                'print_results' => $printResults,
            ]);
        });
    }

    /**
     * Imprime ticket de venta desde el POS (sin información de pago)
     */
    public function printTicket(Order $order)
    {
        try {
            $printerService = new PrinterService;
            $result = $printerService->printSaleTicket($order);

            return response()->json([
                'success' => $result,
                'message' => $result ? 'Ticket impreso correctamente' : 'Error al imprimir ticket',
            ]);
        } catch (\Exception $e) {
            Log::error("Error printing sale ticket: {$order->order_number} - ".$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al imprimir: '.$e->getMessage(),
            ], 500);
        }
    }
}
