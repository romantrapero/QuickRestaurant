<div class="min-h-screen bg-gray-900 text-gray-100" x-data="{ autoRefresh: @js($autoRefresh) }" 
        x-init="
        if (autoRefresh) {
            setInterval(() => {
                console.log('Auto-refresh ejecutado');
                $wire.refreshOrders()
            }, @js($refreshInterval) * 1000);
        }
        ">
    <!-- Header -->
    <div class="bg-gray-800 border-b border-gray-700 px-6 py-4">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-yellow-400 flex items-center gap-2">
                    <i class="fas fa-fire text-red-500"></i>
                    Kitchen Screen Display
                </h1>
                <p class="text-gray-400 text-sm mt-1">
                    <i class="fas fa-clock mr-1"></i>
                    Actualizado: {{ $lastUpdated->format('H:i:s') }}
                    <span x-show="autoRefresh" class="ml-2 text-green-400">
                        <i class="fas fa-sync-alt animate-spin"></i> Auto-refresh
                    </span>
                </p>
            </div>
            
            <!-- Contadores y Filtros -->
            <div class="flex flex-wrap items-center gap-4">
                <!-- Contadores -->
                <div class="flex gap-2">
                    <div class="bg-red-900/40 px-3 py-2 rounded-lg border border-red-700/50 min-w-[80px]">
                        <div class="text-xl font-bold text-red-300 text-center">{{ $counts['pending'] }}</div>
                        <div class="text-xs text-red-400 text-center">Pendientes</div>
                    </div>
                    <div class="bg-blue-900/40 px-3 py-2 rounded-lg border border-blue-700/50 min-w-[80px]">
                        <div class="text-xl font-bold text-blue-300 text-center">{{ $counts['preparing'] }}</div>
                        <div class="text-xs text-blue-400 text-center">Preparando</div>
                    </div>
                    <div class="bg-green-900/40 px-3 py-2 rounded-lg border border-green-700/50 min-w-[80px]">
                        <div class="text-xl font-bold text-green-300 text-center">{{ $counts['ready'] }}</div>
                        <div class="text-xs text-green-400 text-center">Listos</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid de Pedidos -->
    <div class="p-4 md:p-6" id="ordersContainer">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($orders as $order)
                @php
                    $estimatedTime = $this->calculateEstimatedTime($order);
                    $elapsedTime = $this->getElapsedTime($order['created_at']);
                @endphp
                
                <div class="bg-gray-800 rounded-xl border-l-4 
                    @if($order['status'] === 'pending') border-red-500 
                    @elseif($order['status'] === 'preparing') border-blue-500 
                    @else border-green-500 @endif
                    p-4 shadow-lg hover:shadow-xl transition-shadow duration-200"
                    data-order-id="{{ $order['id'] }}"
                    data-status="{{ $order['status'] }}">
                    
                    <!-- Header del Pedido -->
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-lg font-bold text-white">#{{ $order['order_number'] }}</span>
                                <span class="px-2 py-1 text-xs rounded-full 
                                    @if($order['status'] === 'pending') bg-red-900 text-red-300
                                    @elseif($order['status'] === 'preparing') bg-blue-900 text-blue-300
                                    @else bg-green-900 text-green-300 @endif">
                                    {{ $this->translateStatus($order['status']) }}
                                </span>
                            </div>
                            <div class="text-sm text-gray-400 space-y-1">
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-clock text-xs"></i>
                                    {{ $order['created_at']->format('H:i') }} • 
                                    @php
                                        $minutes = $order['created_at']->diffInMinutes(now());
                                        $hours = intdiv($minutes, 60);
                                        $mins = $minutes % 60;
                                        $elapsed = [];
                                        if ($hours > 0) $elapsed[] = $hours . 'h';
                                        if ($mins > 0 || $hours == 0) $elapsed[] = $mins . 'min';
                                        echo implode(' ', $elapsed);
                                    @endphp
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-user text-xs"></i>
                                    {{ $order['customer_name'] ?? 'Cliente no identificado' }}
                                </div>
                                @if($order['table_number'] && $order['table_number'] !== 'Para Llevar')
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-table text-xs"></i>
                                    {{ $order['table_number'] }}
                                </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="text-right">
                            <div class="text-lg font-bold text-yellow-400">
                                ${{ number_format($order['total'], 2) }}
                            </div>
                            <div class="text-xs text-gray-500">
                                @php
                                    $totalItems = array_reduce($order['orderItems'], function($carry, $item) {
                                        return $carry + $item['quantity'];
                                    }, 0);
                                @endphp
                                {{ $totalItems }} ítems
                            </div>
                        </div>
                    </div>
                    
                    <!-- Items del Pedido -->
                    <div class="mb-4 space-y-2 max-h-40 overflow-y-auto pr-2">
                        @foreach($order['orderItems'] as $item)
                            <div class="flex justify-between items-center p-2 bg-gray-900/50 rounded hover:bg-gray-900/70 transition-colors">
                                <div class="flex items-center">
                                    <span class="w-6 h-6 flex items-center justify-center bg-gray-700 rounded text-xs mr-2">
                                        {{ $item['quantity'] }}
                                    </span>
                                    <span class="font-medium">{{ $item['dish']['name'] }}</span>
                                    @if($item['special_instructions'])
                                        <span class="ml-2 text-yellow-400 text-xs" 
                                                title="{{ $item['special_instructions'] }}">
                                            <i class="fas fa-sticky-note"></i>
                                        </span>
                                    @endif
                                </div>
                                <div class="text-sm text-gray-400">
                                    {{ $item['dish']['preparation_time'] ?? 15 }} min
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Notas del Cliente -->
                    @if($order['customer_notes'])
                        <div class="mb-3 p-2 bg-yellow-900/20 border border-yellow-800/50 rounded text-sm">
                            <span class="text-yellow-400 font-medium"><i class="fas fa-sticky-note mr-1"></i>Nota:</span> 
                            <span class="text-yellow-300">{{ Str::limit($order['customer_notes'], 80) }}</span>
                        </div>
                    @endif
                    
                    <!-- Botones de Acción -->
                    <div class="flex gap-2">
                        @if($order['status'] === 'pending')
                            <button x-data x-on:click="$wire.updateOrderStatus({{ $order['id'] }}, 'preparing');" 
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition-colors">
                                <i class="fas fa-play mr-2"></i>
                                Iniciar Preparación
                            </button>
                        @elseif($order['status'] === 'preparing')
                            <button x-data x-on:click="$wire.updateOrderStatus({{ $order['id'] }}, 'ready');"
                                    class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                                <i class="fas fa-check"></i>
                                Marcar como Listo
                            </button>
                        @elseif($order['status'] === 'ready')
                            <button x-data x-on:click="$wire.updateOrderStatus({{ $order['id'] }}, 'delivered');"
                                    class="flex-1 bg-gray-600 hover:bg-gray-700 text-white py-2 px-4 rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                                <i class="fas fa-truck"></i>
                                Entregar
                            </button>
                        @endif
                    </div>
                    
                    <!-- Información adicional -->
                    <div class="mt-3 text-xs text-gray-500 flex justify-between items-center">
                        <div class="flex items-center gap-1">
                            <i class="fas fa-clock"></i>
                            <span>Estimado: {{ $estimatedTime }} min</span>
                        </div>
                        <div class="text-gray-400">
                            {{ $order['created_at']->diffForHumans() }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- JavaScript para funcionalidad estática -->
<script>
    //const getOrders = {!! json_encode($orders->toArray()) !!};
    const getOrders = {!! json_encode(array_values($orders->toArray())) !!};
    
    // Función para mostrar notificación
    function showNotification(message, type = 'info') {
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            info: 'bg-blue-500'
        };
        
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-transform duration-300 translate-x-full flex items-center`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} mr-3"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => notification.classList.replace('translate-x-full', 'translate-x-0'), 10);
        
        setTimeout(() => {
            notification.classList.replace('translate-x-0', 'translate-x-full');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    document.addEventListener('notify', event => {
        const detail = Array.isArray(event.detail) ? event.detail[0] : event.detail;
        showNotification(detail.message, detail.type || 'info');
    });
    
    // Función para filtrar pedidos
    function filterOrders() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const orderCards = document.querySelectorAll('[data-order-id]');
        
        orderCards.forEach(card => {
            const orderNumber = card.querySelector('.text-lg').textContent.toLowerCase();
            const customerName = card.querySelector('.fa-user').parentElement.textContent.toLowerCase();
            const tableNumber = card.querySelector('.fa-table')?.parentElement?.textContent.toLowerCase() || '';
            
            if (orderNumber.includes(searchTerm) || 
                customerName.includes(searchTerm) || 
                tableNumber.includes(searchTerm)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
    
    // Inicializar
    document.addEventListener('DOMContentLoaded', function() {
        showNotification('KSD cargado con {{ $counts['total_active'] }} pedidos activos', 'info');
    });
</script>