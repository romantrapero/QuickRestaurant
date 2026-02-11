<?php

namespace App\Livewire;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.kitchen')]
class KitchenScreen extends Component
{
    public function __construct()
    {
        if (! Auth::check()) {
            abort(403, 'No autorizado');
        }
    }

    public $search = '';

    public $filterStatus = 'active';

    public $sortField = 'created_at';

    public $sortDirection = 'asc';

    public $selectedOrder = null;

    public $autoRefresh = true;

    public $refreshInterval = 5; // in seconds

    public $lastUpdated;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatus' => ['except' => 'active'],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'asc'],
    ];

    private function getOrders()
    {
        $orders = Order::with(['orderItems' => function ($query) {
            $query->where('status', '!=', \App\Models\OrderItem::STATUS_CANCELLED);
        }, 'orderItems.dish'])
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'total' => $order->total,
                    'customer_name' => $order->customer_name,
                    'table_number' => $order->table_number,
                    'customer_notes' => $order->customer_notes,
                    'created_at' => $order->created_at,
                    'orderItems' => $order->orderItems->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'total_price' => $item->total_price,
                            'special_instructions' => $item->special_instructions,
                            'dish' => [
                                'name' => $item->dish->name ?? null,
                                'preparation_time' => $item->dish->preparation_time ?? null,
                            ],
                        ];
                    })->toArray(),
                ];
            });

        return collect($orders);
    }

    public function mount()
    {
        if (! Auth::check()) {
            abort(403, 'No autorizado');
        }
        $this->lastUpdated = now();
    }

    // Obtener pedidos según filtros
    public function getOrdersQuery()
    {
        $orders = $this->getOrders();

        // Aplicar filtro de estado
        if ($this->filterStatus === 'active') {
            $orders = $orders->whereIn('status', ['pending', 'preparing', 'ready']);
        } elseif ($this->filterStatus === 'delivered') {
            $orders = $orders->where('status', 'delivered');
        }

        // Aplicar búsqueda
        if ($this->search) {
            $orders = $orders->filter(function ($order) {
                return stripos($order['order_number'], $this->search) !== false ||
                       stripos($order['customer_name'], $this->search) !== false ||
                       stripos($order['table_number'], $this->search) !== false;
            });
        }

        // Aplicar ordenamiento
        $orders = $orders->sortBy($this->sortField, SORT_REGULAR, $this->sortDirection === 'desc');

        return $orders;
    }

    // Método para actualizar estado del pedido
    public function updateOrderStatus($orderId, $status)
    {
        $order = Order::find($orderId);

        if (! $order) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Pedido no encontrado',
            ]);

            return;
        }

        $order->status = $status;
        $order->save();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Pedido #{$order['order_number']} actualizado a: ".$this->translateStatus($status),
        ]);

        // Actualizar el tiempo de última modificación
        $this->lastUpdated = now();
    }

    // Método para ver detalles del pedido
    public function viewOrderDetails($orderId)
    {
        $this->selectedOrder = $this->getOrders()->firstWhere('id', $orderId);
    }

    // Método para cerrar detalles
    public function closeOrderDetails()
    {
        $this->selectedOrder = null;
    }

    // Método para refrescar manualmente
    public function refreshOrders()
    {
        $this->lastUpdated = now();
        /*$this->dispatch('notify', [
            'type' => 'info',
            'message' => 'Pedidos actualizados'
        ]);*/
    }

    // Contadores de pedidos
    public function getCountsProperty()
    {
        $orders = $this->getOrders();

        return [
            'pending' => $orders->where('status', 'pending')->count(),
            'preparing' => $orders->where('status', 'preparing')->count(),
            'ready' => $orders->where('status', 'ready')->count(),
            'total_active' => $orders->whereIn('status', ['pending', 'preparing'])->count(),
        ];
    }

    // Traducción de estados
    private function translateStatus($status)
    {
        $translations = [
            'pending' => 'Pendiente',
            'preparing' => 'En Preparación',
            'ready' => 'Listo',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado',
        ];

        return $translations[$status] ?? $status;
    }

    // Calcular tiempo transcurrido
    public function getElapsedTime($createdAt)
    {
        $now = now();
        $created = is_string($createdAt) ? \Carbon\Carbon::parse($createdAt) : $createdAt;
        $minutes = $created->diffInMinutes($now);

        if ($minutes < 1) {
            return 'Ahora';
        } elseif ($minutes < 60) {
            return "{$minutes} min";
        } else {
            $hours = floor($minutes / 60);
            $remainingMinutes = $minutes % 60;

            return "{$hours}h {$remainingMinutes}min";
        }
    }

    // Calcular tiempo estimado de preparación
    public function calculateEstimatedTime($order)
    {
        if (! isset($order['orderItems'])) {
            return 0;
        }

        $totalTime = 0;
        foreach ($order['orderItems'] as $item) {
            $prepTime = $item['dish']['preparation_time'] ?? 15;
            $totalTime += $prepTime * $item['quantity'];
        }

        return $totalTime;
    }

    // Método principal de renderizado
    public function render()
    {
        $orders = $this->getOrdersQuery();

        return view('livewire.kitchen-screen', [
            'orders' => $orders,
            'counts' => $this->counts,
        ]);
    }
}
