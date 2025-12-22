<div wire:id="pos-screen">
    <div class="min-h-screen">
        <!-- Header -->
        <div class="bg-white shadow-lg" wire:ignore>
            <div class="container mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">
                            <i class="fas fa-utensils text-blue-500 mr-2"></i>
                            QuickRestaurant OMS
                        </h1>
                        <p class="text-gray-600">Sistema de Gestión de Órdenes para Restaurantes</p>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-blue-600" id="current-time">--:--:--</div>
                        <div class="text-gray-500" id="current-date">---</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido Principal -->
        <div class="container mx-auto px-4 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Panel Izquierdo - Menú -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">
                            <i class="fas fa-list-alt text-green-500 mr-2"></i>
                            Menú Disponible
                        </h2>
                        
                        <!-- Categorías -->
                        <div class="mb-6">
                            <div class="flex flex-wrap gap-2">
                                <button wire:click="$set('activeCategory', null)"
                                    class="category-btn px-4 py-2 rounded-lg font-semibold transition {{ is_null($activeCategory) ? 'active bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                                    Todos
                                </button>
                                @foreach($categories as $category)
                                    <button wire:click="$set('activeCategory', {{ $category->id }})"
                                        class="category-btn px-4 py-2 rounded-lg font-semibold transition {{ $activeCategory == $category->id ? 'active bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                                        {{ $category->name }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Grid de Productos -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" wire:key="dishes-{{ $activeCategory }}">
                            @forelse($dishes as $dish)
                                <div class="dish-card border border-gray-200 rounded-xl p-4 hover:shadow-md transition cursor-pointer bg-white">
                                    <div>
                                        <h3 class="font-bold text-lg text-gray-800">{{ $dish->name }}</h3>
                                        @if(!empty($dish->image_url))
                                            <img src="{{ asset('storage/dishes/' . $dish->image_url) }}" alt="{{ $dish->name }}" class="w-full h-32 object-cover rounded-lg shadow mb-2">
                                        @else
                                            <div class="w-full h-32 bg-gray-200 flex items-center justify-center rounded-lg mb-2 text-gray-400">
                                                <i class="fas fa-image fa-2x"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="mt-4 flex justify-between items-center">
                                        <div>
                                            <div class="text-2xl font-bold text-blue-600">${{ number_format($dish->sale_price, 2) }}</div>
                                            {{-- <div class="text-xs text-gray-500">
                                                Costo: ${{ number_format($dish->cost_price, 2) }} • 
                                                Margen: ${{ number_format($dish->sale_price - $dish->cost_price, 2) }}
                                            </div> --}}
                                        </div>
                                        <button class="bg-blue-500 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-600 transition"
                                                onclick="addToCart({{ $dish->id }}, '{{ addslashes($dish->name) }}', {{ $dish->sale_price }}, this)">
                                            <i class="fas fa-plus mr-1"></i> Agregar
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-3 text-center text-gray-500 py-8">
                                    No hay productos disponibles en esta categoría.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Panel Derecho - Carrito (wire:ignore para que Livewire no lo toque) -->
                <div class="lg:col-span-1" wire:ignore>
                    <div class="bg-white rounded-xl shadow-lg p-6 sticky top-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6 pb-3">
                            <i class="fas fa-shopping-cart text-orange-500 mr-2"></i>
                            Orden Actual
                        </h2>

                        <!-- Items del Carrito -->
                        <div id="cart-items" class="space-y-4 mb-6 max-h-96 overflow-y-auto pr-2">
                            <div class="text-center py-8 text-gray-500" id="empty-cart-message">
                                <i class="fas fa-shopping-cart text-4xl text-gray-300 mb-3"></i>
                                <p>El carrito está vacío</p>
                            </div>
                        </div>
                        <!-- Agrega esto dentro de la sección del carrito, antes de "Totales" -->
                        <div class="mb-6 space-y-4">
                            <div class="flex flex-col md:flex-row gap-4">
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        <i class="fas fa-concierge-bell mr-1"></i> Tipo de Orden
                                    </label>
                                    <select id="table-type" class="w-full border border-gray-300 rounded-lg p-2">
                                        <option value="">Seleccionar...</option>
                                        <option value="To Go">To Go</option>
                                        <option value="Didi Food">Didi Food</option>
                                        <option value="Uber Eats">Uber Eats</option>
                                        <option value="Rappi">Rappi</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                </div>
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        <i class="fas fa-user mr-1"></i> Cliente
                                    </label>
                                    <input type="text" id="customer-name" placeholder="Nombre del cliente" 
                                        class="w-full border border-gray-300 rounded-lg p-2">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-sticky-note mr-1"></i> Notas del Pedido
                                </label>
                                <textarea id="order-notes" rows="2" placeholder="Instrucciones especiales..." 
                                        class="w-full border border-gray-300 rounded-lg p-2"></textarea>
                            </div>
                        </div>

                        <!-- También agrega el contador de items que falta: -->
                        <div class="flex items-center justify-between mt-2">
                            <div class="flex items-center">
                                <i class="fas fa-clock text-orange-500 mr-2"></i>
                                <span>Hora: <span id="order-time">{{ date('H:i') }}</span></span>
                            </div>
                            <span id="item-count" class="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded">
                                0 items
                            </span>
                        </div>

                        <!-- Totales -->
                        <div class="border-t pt-4 space-y-3">
                            <div class="flex justify-between text-lg">
                                <span class="text-gray-600">Subtotal:</span>
                                <span class="font-semibold" id="subtotal">$0.00</span>
                            </div>
                            <div class="flex justify-between text-lg">
                                <span class="text-gray-600">IVA (16%):</span>
                                <span class="font-semibold" id="tax">$0.00</span>
                            </div>
                            <div class="flex justify-between text-2xl font-bold text-gray-800 border-t pt-3 mt-3">
                                <span>TOTAL:</span>
                                <span class="text-blue-600" id="total">$0.00</span>
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="mt-8 space-y-3">
                            <button id="confirm-order" class="w-full bg-green-500 text-white py-4 rounded-xl font-bold text-lg hover:bg-green-600 transition flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                <i class="fas fa-check-circle mr-2"></i>
                                <span>Confirmar Orden</span>
                                <span id="total-text"> ($0.00)</span>
                            </button>
                            
                            <button id="clear-cart" class="w-full bg-red-500 text-white py-3 rounded-xl font-semibold hover:bg-red-600 transition">
                                <i class="fas fa-trash mr-2"></i>
                                Limpiar Todo
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    // =============================================
    // SISTEMA DE CARRITO PARA QUICKRESTAURANT POS
    // =============================================

    // Carrito en memoria con persistencia en localStorage
    let cart = JSON.parse(localStorage.getItem('quickrestaurant_cart')) || [];

    // =============================================
    // INICIALIZACIÓN
    // =============================================
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🔄 Inicializando sistema POS...');
        
        // Configurar eventos
        setupEventListeners();
        
        // Actualizar visualizaciones
        updateCartDisplay();
        updateTime();
        
        // Auto-actualizar hora
        setInterval(updateTime, 1000);
        
        console.log('✅ Sistema POS inicializado');
        console.log('📦 Carrito cargado:', cart.length, 'items');
    });

    // =============================================
    // CONFIGURACIÓN DE EVENTOS
    // =============================================
    function setupEventListeners() {
        const clearCartBtn = document.getElementById('clear-cart');
        const confirmOrderBtn = document.getElementById('confirm-order');
        
        if (clearCartBtn) {
            clearCartBtn.addEventListener('click', clearCart);
            console.log('✅ Botón clear-cart configurado');
        }
        
        if (confirmOrderBtn) {
            confirmOrderBtn.addEventListener('click', handleConfirmOrder);
            console.log('✅ Botón confirm-order configurado');
        }
        
        // Verificar que los elementos existan
        if (!clearCartBtn) console.warn('⚠️ Botón clear-cart no encontrado');
        if (!confirmOrderBtn) console.warn('⚠️ Botón confirm-order no encontrado');
    }

    // =============================================
    // GESTIÓN DEL CARRITO
    // =============================================
    function addToCart(id, name, price, buttonElement) {
        console.log('➕ Agregando al carrito:', { id, name, price });
        
        // Buscar si ya existe en el carrito
        const existingItemIndex = cart.findIndex(item => item.id === id);
        
        if (existingItemIndex !== -1) {
            // Incrementar cantidad si ya existe
            cart[existingItemIndex].quantity += 1;
            cart[existingItemIndex].total = cart[existingItemIndex].quantity * cart[existingItemIndex].price;
        } else {
            // Agregar nuevo item
            cart.push({
                id: id,
                name: name,
                price: parseFloat(price),
                quantity: 1,
                total: parseFloat(price)
            });
        }
        
        // Guardar en localStorage
        saveCart();
        
        // Actualizar visualización
        updateCartDisplay();
        
        // Efecto visual en el botón
        if (buttonElement) {
            animateAddButton(buttonElement);
        }
        
        // Notificación
        showNotification(`${name} agregado al carrito`, 'success');
    }

    function updateQuantity(id, change) {
        const itemIndex = cart.findIndex(item => item.id === id);
        
        if (itemIndex !== -1) {
            cart[itemIndex].quantity += change;
            
            if (cart[itemIndex].quantity <= 0) {
                // Eliminar si la cantidad es 0 o menos
                cart.splice(itemIndex, 1);
                showNotification('Item eliminado', 'info');
            } else {
                // Actualizar total
                cart[itemIndex].total = cart[itemIndex].quantity * cart[itemIndex].price;
            }
            
            saveCart();
            updateCartDisplay();
        }
    }

    function removeFromCart(id) {
        const itemIndex = cart.findIndex(item => item.id === id);
        
        if (itemIndex !== -1) {
            const itemName = cart[itemIndex].name;
            cart.splice(itemIndex, 1);
            
            saveCart();
            updateCartDisplay();
            showNotification(`${itemName} eliminado`, 'info');
        }
    }

    function clearCart() {
        if (cart.length === 0) {
            showNotification('El carrito ya está vacío', 'info');
            return;
        }
        
        if (confirm(`¿Eliminar ${cart.length} items del carrito?`)) {
            cart = [];
            localStorage.removeItem('quickrestaurant_cart');
            updateCartDisplay();
            showNotification('Carrito vacío', 'success');
        }
    }

    function saveCart() {
        localStorage.setItem('quickrestaurant_cart', JSON.stringify(cart));
    }

    // =============================================
    // VISUALIZACIÓN DEL CARRITO
    // =============================================
    function updateCartDisplay() {
        const cartItemsContainer = document.getElementById('cart-items');
        const emptyCartMessage = document.getElementById('empty-cart-message');
        const confirmButton = document.getElementById('confirm-order');
        const itemCountElement = document.getElementById('item-count');
        
        if (!cartItemsContainer) {
            console.error('❌ Contenedor cart-items no encontrado');
            return;
        }
        
        // Actualizar contador de items
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        if (itemCountElement) {
            itemCountElement.textContent = `${totalItems} ${totalItems === 1 ? 'item' : 'items'}`;
        }
        
        // Manejar carrito vacío
        if (cart.length === 0) {
            // Si el mensaje de carrito vacío existe, mostrarlo
            if (emptyCartMessage) {
                emptyCartMessage.style.display = 'block';
                cartItemsContainer.innerHTML = '';
                cartItemsContainer.appendChild(emptyCartMessage);
            } else {
                // Si no existe, crear uno nuevo
                cartItemsContainer.innerHTML = `
                    <div class="text-center py-8 text-gray-500" id="empty-cart-message">
                        <i class="fas fa-shopping-cart text-4xl text-gray-300 mb-3"></i>
                        <p>El carrito está vacío</p>
                        <p class="text-sm mt-1">Agrega productos del menú</p>
                    </div>
                `;
            }
            
            if (confirmButton) {
                confirmButton.disabled = true;
                confirmButton.classList.add('opacity-50', 'cursor-not-allowed');
            }
            
            updateTotals(0);
            return;
        }
        
        // Ocultar mensaje de carrito vacío si existe
        if (emptyCartMessage) {
            emptyCartMessage.style.display = 'none';
        }
        
        // Generar HTML de los items
        let cartHTML = '';
        let subtotal = 0;
        
        cart.forEach(item => {
            subtotal += item.total;
            cartHTML += `
                <div class="cart-item flex justify-between items-center border-b pb-3">
                    <div class="flex-1">
                        <div class="font-semibold text-gray-800">${item.name}</div>
                        <div class="text-sm text-gray-600">$${item.price.toFixed(2)} c/u</div>
                        <div class="flex items-center mt-2">
                            <button onclick="updateQuantity(${item.id}, -1)" 
                                    class="w-7 h-7 bg-gray-200 rounded flex items-center justify-center hover:bg-gray-300 transition">
                                <i class="fas fa-minus text-xs"></i>
                            </button>
                            <span class="mx-3 font-medium text-center min-w-[20px]">${item.quantity}</span>
                            <button onclick="updateQuantity(${item.id}, 1)" 
                                    class="w-7 h-7 bg-gray-200 rounded flex items-center justify-center hover:bg-gray-300 transition">
                                <i class="fas fa-plus text-xs"></i>
                            </button>
                            <button onclick="removeFromCart(${item.id})" 
                                    class="ml-4 text-red-500 hover:text-red-700 transition">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-bold text-gray-800">$${item.total.toFixed(2)}</div>
                    </div>
                </div>
            `;
        });
        
        cartItemsContainer.innerHTML = cartHTML;
        
        if (confirmButton) {
            confirmButton.disabled = false;
            confirmButton.classList.remove('opacity-50', 'cursor-not-allowed');
        }
        
        // Actualizar totales
        updateTotals(subtotal);
    }

    function updateTotals(subtotal) {
        const tax = subtotal * 0.16; // 16% IVA
        const total = subtotal + tax;
        
        const subtotalEl = document.getElementById('subtotal');
        const taxEl = document.getElementById('tax');
        const totalEl = document.getElementById('total');
        const totalText = document.getElementById('total-text');
        
        if (subtotalEl) subtotalEl.textContent = `$${subtotal.toFixed(2)}`;
        if (taxEl) taxEl.textContent = `$${tax.toFixed(2)}`;
        if (totalEl) totalEl.textContent = `$${total.toFixed(2)}`;
        if (totalText) totalText.textContent = ` ($${total.toFixed(2)})`;
    }

    // =============================================
    // CONFIRMACIÓN DE ORDEN (CORREGIDA)
    // =============================================
    async function handleConfirmOrder() {
        console.log('📝 Confirmando orden...');
        
        // Validaciones básicas
        if (cart.length === 0) {
            showNotification('El carrito está vacío', 'error');
            return;
        }
        
        const tableType = document.getElementById('table-type')?.value;
        if (!tableType) {
            showNotification('Selecciona una mesa o tipo de orden', 'error');
            document.getElementById('table-type').focus();
            return;
        }
        
        const customerName = document.getElementById('customer-name')?.value || 'Cliente no identificado';
        const notes = document.getElementById('order-notes')?.value || '';
        
        // Preparar datos para enviar
        const orderData = {
            table: tableType,
            customer: customerName,
            notes: notes,
            items: cart.map(item => ({
                id: item.id,
                price: item.price,
                quantity: item.quantity
            }))
        };
        
        console.log('📤 Datos de la orden:', orderData);
        
        // Mostrar estado de carga
        const confirmBtn = document.getElementById('confirm-order');
        const originalText = confirmBtn.innerHTML;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Procesando...';
        confirmBtn.disabled = true;
        
        try {
            // Enviar al backend (usando fetch directamente)
            const response = await sendOrderToBackend(orderData);
            
            if (response.success) {
                // Éxito: mostrar confirmación y limpiar
                showOrderSuccessModal(response.order);
                
                // Limpiar carrito y formulario
                cart = [];
                localStorage.removeItem('quickrestaurant_cart');
                
                // Usar setTimeout para asegurar que el DOM esté listo
                setTimeout(() => {
                    updateCartDisplay();
                }, 100);
                
                document.getElementById('table-type').value = '';
                document.getElementById('customer-name').value = '';
                document.getElementById('order-notes').value = '';
                
                showNotification('¡Orden creada exitosamente!', 'success');
            } else {
                // Error del servidor
                throw new Error(response.message || 'Error al crear la orden');
            }
        } catch (error) {
            console.error('❌ Error al confirmar orden:', error);
            showNotification(error.message || 'Error al procesar la orden', 'error');
        } finally {
            // Restaurar botón
            confirmBtn.innerHTML = originalText;
            confirmBtn.disabled = cart.length === 0;
        }
    }

    async function sendOrderToBackend(orderData) {
        // Obtener CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        if (!csrfToken) {
            console.error('❌ CSRF token no encontrado');
            throw new Error('Error de seguridad. Recarga la página.');
        }
        
        const response = await fetch('/api/orders', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(orderData)
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || `Error ${response.status}`);
        }
        
        return data;
    }

    // =============================================
    // UI & UTILIDADES
    // =============================================
    function animateAddButton(button) {
        const originalHTML = button.innerHTML;
        const originalClasses = button.className;
        
        button.innerHTML = '<i class="fas fa-check mr-1"></i> ¡Agregado!';
        button.className = originalClasses.replace('bg-blue-500', 'bg-green-500').replace('hover:bg-blue-600', 'hover:bg-green-600');
        
        setTimeout(() => {
            button.innerHTML = originalHTML;
            button.className = originalClasses;
        }, 1000);
    }

    function showNotification(message, type = 'info') {
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            info: 'bg-blue-500'
        };
        
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-transform duration-300 translate-x-full flex items-center`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'} mr-3"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => notification.classList.replace('translate-x-full', 'translate-x-0'), 10);
        
        setTimeout(() => {
            notification.classList.replace('translate-x-0', 'translate-x-full');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    function showOrderSuccessModal(order) {
        const modalHTML = `
            <div class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-2xl p-8 max-w-md w-full">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-check text-green-500 text-2xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800">¡Orden Confirmada!</h3>
                        <p class="text-gray-600 mt-2">${order.order_number}</p>
                    </div>
                    
                    <div class="border-t border-b py-4 my-4">
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">Total:</span>
                            <span class="font-semibold">$${parseFloat(order.total).toFixed(2)}</span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">Estado:</span>
                            <span class="font-semibold ${order.status === 'pending' ? 'text-yellow-600' : 'text-green-600'}">
                                ${order.status === 'pending' ? 'Pendiente' : 'En preparación'}
                            </span>
                        </div>
                    </div>
                    
                    <button onclick="this.closest('.fixed').remove()" 
                            class="w-full bg-blue-500 text-white py-3 rounded-lg font-semibold hover:bg-blue-600 transition">
                        Aceptar
                    </button>
                </div>
            </div>
        `;
        
        const modal = document.createElement('div');
        modal.innerHTML = modalHTML;
        document.body.appendChild(modal);
    }

    function updateTime() {
        const now = new Date();
        const timeElement = document.getElementById('current-time');
        const dateElement = document.getElementById('current-date');
        const orderTimeElement = document.getElementById('order-time');
        
        if (timeElement) {
            timeElement.textContent = now.toLocaleTimeString('es-MX', { 
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }
        
        if (dateElement) {
            const dateString = now.toLocaleDateString('es-MX', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            dateElement.textContent = dateString.charAt(0).toUpperCase() + dateString.slice(1);
        }
        
        if (orderTimeElement) {
            orderTimeElement.textContent = now.toLocaleTimeString('es-MX', { 
                hour12: true,
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }

    // =============================================
    // EXPORTAR FUNCIONES GLOBALES
    // =============================================
    window.addToCart = addToCart;
    window.updateQuantity = updateQuantity;
    window.removeFromCart = removeFromCart;
    window.clearCart = clearCart;
    window.handleConfirmOrder = handleConfirmOrder;
</script>