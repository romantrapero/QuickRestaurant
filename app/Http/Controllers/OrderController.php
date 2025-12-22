<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar datos de entrada
        $validated = $request->validate([
            'table' => 'required|string|max:255',
            'customer' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:dishes,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        // Usar transacción para asegurar integridad
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
                ]);
                
                $total += $orderItem->total_price;
            }
            
            // Actualizar el total de la orden
            $order->update(['total' => $total]);
            
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
            ], 201);
        });
    }

    private function generateOrderNumber(): string
    {
        $date = date('Ymd');
        $latest = Order::whereDate('created_at', today())->count();
        
        return 'ORD-' . $date . '-' . str_pad($latest + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $order->load('items.dish');
        
        return response()->json([
            'success' => true,
            'order' => $order,
            'items' => $order->items,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
