<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

class PosScreen extends Component
{
    public function __construct()
    {
        if (!Auth::check()) {
            abort(403, 'No autorizado');
        }
    }
    public $activeCategory = null;

    public function placeOrder($cartData, $table, $customer, $notes)
    {
        // Validar datos de entrada
        $validator = Validator::make([
            'cart' => $cartData,
            'table' => $table,
            'customer' => $customer,
        ], [
            'cart' => 'required|array|min:1',
            'cart.*.id' => 'required|exists:dishes,id',
            'cart.*.quantity' => 'required|integer|min:1',
            'cart.*.price' => 'required|numeric|min:0',
        ]);
        
        if ($validator->fails()) {
            return ['success' => false, 'message' => 'Datos inválidos', 'errors' => $validator->errors()];
        }

        // Calcular total
        $total = 0;
        foreach ($cartData as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        // Crear la orden
        $order = Order::create([
            'order_number' => 'ORD-' . date('Ymd') . '-' . Str::upper(Str::random(4)),
            'status' => 'pending',
            'total' => $total,
            'table_number' => $table,
            'customer_notes' => $notes,
            'customer_name' => $customer,
        ]);

        // Crear los items de la orden
        foreach ($cartData as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'dish_id' => $item['id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
                'total_price' => $item['price'] * $item['quantity'],
                'special_instructions' => $item['special_instructions'] ?? null,
            ]);
        }

        // Devolver respuesta de éxito
        return [
            'success' => true, 
            'message' => 'Orden creada exitosamente',
            'order_number' => $order->order_number
        ];
    }
    
    public function render()
    {
        if (!Auth::check()) {
            abort(403, 'No autorizado');
        }
        $categories = Category::where('is_active', true)
            ->orderBy('order')
            ->get();
            
        $dishes = Dish::where('is_available', true)
            ->when($this->activeCategory, function ($query) {
                return $query->where('category_id', $this->activeCategory);
            })
            ->with('category')
            ->get();
            
        return view('livewire.pos-screen', [
            'categories' => $categories,
            'dishes' => $dishes,
        ]);
    }
}