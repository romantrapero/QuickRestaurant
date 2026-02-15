<div wire:id="pos-screen" class="pos-viewport">
    <div class="flex-1 min-h-0 overflow-y-auto pos-content-scroll">
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
                    <div class="flex items-center gap-4">
                        {{-- Shift Status Bar --}}
                        <div id="shift-status-bar" class="flex items-center gap-2">
                            {{-- Populated by JavaScript --}}
                        </div>
                        <div class="text-right">
                            <a href="/mesas"
                               class="inline-flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold transition mb-2">
                                <i class="fas fa-th-large"></i>
                                Gestión de Mesas
                            </a>
                            <div class="text-2xl font-bold text-blue-600" id="current-time">--:--:--</div>
                            <div class="text-gray-500" id="current-date">---</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido Principal -->
        <div class="container mx-auto px-5 py-5">
            <div class="grid grid-cols-1 lg:grid-cols-3" style="gap: 0.5rem;">
                <!-- Panel Izquierdo - Menú -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-7">
                        <h2 class="text-xl font-bold text-gray-800 mb-5">
                            <i class="fas fa-list-alt text-green-500 mr-2"></i>
                            Menú Disponible
                        </h2>
                        
                        <!-- Categorías -->
                        <div class="mb-5">
                            <div class="flex flex-wrap gap-2">
                                <button wire:click="$set('activeCategory', null)"
                                    class="category-btn px-4 py-1.5 rounded-full text-sm font-semibold transition {{ is_null($activeCategory) ? 'active bg-blue-500 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                    Todos
                                </button>
                                @foreach($categories as $category)
                                    <button wire:click="$set('activeCategory', {{ $category->id }})"
                                        class="category-btn px-4 py-1.5 rounded-full text-sm font-semibold transition {{ $activeCategory == $category->id ? 'active bg-blue-500 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                        {{ $category->name }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Grid de Productos -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3" wire:key="dishes-{{ $activeCategory }}">
                            @forelse($dishes as $dish)
                                <div class="dish-card flex flex-col border border-gray-200/80 rounded-2xl overflow-hidden bg-white active:scale-[0.97] shadow-sm"
                                     onclick="addToCart({{ $dish->id }}, '{{ addslashes($dish->name) }}', {{ $dish->sale_price }}, this.querySelector('.add-btn'))">
                                    @if(!empty($dish->image_url))
                                        <img src="{{ asset('storage/dishes/' . $dish->image_url) }}" alt="{{ $dish->name }}" class="w-full h-28 object-cover">
                                    @else
                                        <div class="w-full h-28 bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center text-gray-300">
                                            <i class="fas fa-utensils text-2xl"></i>
                                        </div>
                                    @endif
                                    <div class="flex flex-col grow p-3">
                                        <h3 class="font-semibold text-[13px] text-gray-800 leading-tight line-clamp-2 mb-auto">{{ $dish->name }}</h3>
                                        <div class="flex justify-between items-center mt-2">
                                            <span class="text-lg font-bold text-gray-900">${{ number_format($dish->sale_price, 2) }}</span>
                                            <span class="add-btn inline-flex items-center justify-center w-8 h-8 bg-blue-500 text-white rounded-full text-sm shadow-sm">
                                                <i class="fas fa-plus"></i>
                                            </span>
                                        </div>
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
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-7 sticky top-4">
                        <h2 class="text-xl font-bold text-gray-800 mb-5 pb-2">
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
                                        @php $mesasActivas = $this->getMesasActivas(); @endphp
                                        @if($mesasActivas->count() > 0)
                                        <optgroup label="Mesas">
                                            @foreach($mesasActivas as $mesa)
                                            <option value="Mesa {{ $mesa->numero }}">Mesa {{ $mesa->numero }}{{ $mesa->nombre ? ' - '.$mesa->nombre : '' }}</option>
                                            @endforeach
                                        </optgroup>
                                        @endif
                                        <optgroup label="Delivery / Otros">
                                            <option value="To Go">To Go</option>
                                            <option value="Didi Food">Didi Food</option>
                                            <option value="Uber Eats">Uber Eats</option>
                                            <option value="Rappi">Rappi</option>
                                            <option value="Otro">Otro</option>
                                        </optgroup>
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
                            <div class="flex justify-between text-2xl font-bold text-gray-800">
                                <span>TOTAL:</span>
                                <span class="text-blue-600" id="total">$0.00</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-500">
                                <span>Subtotal sin IVA:</span>
                                <span id="subtotal">$0.00</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-500">
                                <span>IVA incluido (16%):</span>
                                <span id="tax">$0.00</span>
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

    </div>{{-- cierra div scrollable --}}

    {{-- ===== BARRA ORDENES ACTIVAS ===== --}}
    <div id="orders-bar" onclick="toggleOrdersDrawer()"
         class="shrink-0"
         style="background:#059669; color:#fff; padding:14px 20px; padding-bottom:calc(14px + env(safe-area-inset-bottom, 0px)); display:flex; align-items:center; justify-content:space-between; cursor:pointer; box-shadow:0 -2px 10px rgba(0,0,0,0.15);">
            <div style="display:flex; align-items:center; gap:12px;">
                <span id="orders-count" style="background:#fff; color:#059669; font-weight:700; font-size:14px; min-width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; padding:0 6px;">0</span>
                <span id="orders-label" style="font-weight:600;">
                    <i class="fas fa-receipt mr-2"></i>Órdenes Activas
                </span>
            </div>
            <span style="font-weight:500;">
                <i class="fas fa-chevron-up"></i>
            </span>
        </div>

        {{-- ===== BACKDROP ORDENES ===== --}}
        <div id="orders-backdrop" onclick="toggleOrdersDrawer()"
             style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:40; opacity:0; pointer-events:none; transition:opacity 0.3s ease;"></div>

        {{-- ===== DRAWER ORDENES ACTIVAS ===== --}}
        <div id="orders-drawer"
             style="position:fixed; bottom:0; left:0; right:0; z-index:50; background:#fff; border-radius:16px 16px 0 0; box-shadow:0 -4px 20px rgba(0,0,0,0.15); max-height:85vh; display:flex; flex-direction:column; transform:translateY(100%); transition:transform 0.3s ease;">

            {{-- Handle --}}
            <div style="display:flex; justify-content:center; padding:12px 0 4px; cursor:pointer;" onclick="toggleOrdersDrawer()">
                <div style="width:40px; height:4px; background:#d1d5db; border-radius:2px;"></div>
            </div>

            {{-- Header --}}
            <div style="padding:0 16px 12px; border-bottom:1px solid #e5e7eb;">
                <h2 style="font-size:18px; font-weight:700; color:#1f2937; margin:0;">
                    <i class="fas fa-clipboard-list text-emerald-600 mr-2"></i> Órdenes por Cobrar
                </h2>
            </div>

            {{-- Lista de ordenes (scrollable) --}}
            <div id="orders-list" style="flex:1; overflow-y:auto; padding:12px 16px;">
                <p class="text-center text-gray-400 py-8">Cargando órdenes...</p>
            </div>
        </div>

        {{-- ===== DRAWER DETALLE ORDEN + COBRO ===== --}}
        <div id="order-detail-drawer"
             style="position:fixed; bottom:0; left:0; right:0; z-index:55; background:#fff; border-radius:16px 16px 0 0; box-shadow:0 -4px 20px rgba(0,0,0,0.15); max-height:90vh; display:flex; flex-direction:column; transform:translateY(100%); transition:transform 0.3s ease;">

            {{-- Handle --}}
            <div style="display:flex; justify-content:center; padding:12px 0 4px; cursor:pointer;" onclick="closeOrderDetail()">
                <div style="width:40px; height:4px; background:#d1d5db; border-radius:2px;"></div>
            </div>

            {{-- Header con numero de orden --}}
            <div style="padding:0 16px 12px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h2 id="detail-order-number" style="font-size:18px; font-weight:700; color:#1f2937; margin:0;"></h2>
                    <p id="detail-table-info" style="font-size:14px; color:#6b7280; margin:4px 0 0;"></p>
                </div>
                <div style="display:flex; gap:8px; align-items:center;">
                    <button onclick="printSaleTicket()" class="inline-flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold transition text-sm">
                        <i class="fas fa-print"></i>
                        Imprimir Ticket
                    </button>
                    <button onclick="closeOrderDetail()" style="background:#f3f4f6; border:none; border-radius:50%; width:36px; height:36px; cursor:pointer; display:flex; align-items:center; justify-content:center;">
                        <i class="fas fa-times text-gray-600"></i>
                    </button>
                </div>
            </div>

            {{-- Contenido scrollable --}}
            <div style="flex:1; overflow-y:auto; padding:16px;">
                {{-- Items de la orden --}}
                <div id="detail-items" style="margin-bottom:16px;"></div>

                {{-- Resumen de pagos --}}
                <div id="payment-summary" style="background:#f9fafb; border-radius:12px; padding:12px; margin-bottom:16px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                        <span style="color:#6b7280;">Total:</span>
                        <span id="detail-total" style="font-weight:700;"></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                        <span style="color:#6b7280;">Pagado:</span>
                        <span id="detail-paid" style="font-weight:600; color:#059669;"></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; border-top:1px solid #e5e7eb; padding-top:8px;">
                        <span style="font-weight:600;">Restante:</span>
                        <span id="detail-remaining" style="font-weight:700; color:#dc2626;"></span>
                    </div>
                </div>

                {{-- Formulario de cobro --}}
                <div id="payment-form" style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:16px;">
                    <h3 style="font-weight:600; margin:0 0 12px; font-size:16px;">
                        <i class="fas fa-cash-register text-emerald-600 mr-2"></i>Registrar Pago
                    </h3>

                    {{-- Metodo de pago --}}
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:14px; color:#374151; margin-bottom:6px;">Método</label>
                        <div style="display:flex; gap:8px;">
                            <button type="button" class="payment-method-btn active" data-method="efectivo"
                                    style="flex:1; padding:10px; border:2px solid #059669; background:#ecfdf5; border-radius:8px; cursor:pointer; font-weight:500; font-size:13px;">
                                <i class="fas fa-money-bill-wave mr-1"></i> Efectivo
                            </button>
                            <button type="button" class="payment-method-btn" data-method="tarjeta"
                                    style="flex:1; padding:10px; border:2px solid #e5e7eb; background:#fff; border-radius:8px; cursor:pointer; font-weight:500; font-size:13px;">
                                <i class="fas fa-credit-card mr-1"></i> Tarjeta
                            </button>
                            <button type="button" class="payment-method-btn" data-method="transferencia"
                                    style="flex:1; padding:10px; border:2px solid #e5e7eb; background:#fff; border-radius:8px; cursor:pointer; font-weight:500; font-size:13px;">
                                <i class="fas fa-university mr-1"></i> Transf.
                            </button>
                        </div>
                    </div>

                    {{-- Monto --}}
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:14px; color:#374151; margin-bottom:6px;">Monto</label>
                        <div style="display:flex; gap:8px; align-items:center;">
                            <input type="number" id="payment-amount" step="0.01" min="0"
                                   style="flex:1; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-size:16px;">
                            <button type="button" onclick="setFullPayment()"
                                    style="background:#3b82f6; color:#fff; padding:10px 16px; border-radius:8px; border:none; cursor:pointer; font-weight:500;">
                                Total
                            </button>
                        </div>
                    </div>

                    {{-- Propina --}}
                    <div style="margin-bottom:16px;">
                        <label style="display:block; font-size:14px; color:#374151; margin-bottom:6px;">Propina (opcional)</label>
                        <input type="number" id="payment-tip" step="0.01" min="0" value="0"
                               style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-size:16px; box-sizing:border-box;">
                    </div>

                    {{-- Boton de cobrar --}}
                    <button id="process-payment-btn" onclick="processPayment()"
                            style="width:100%; background:#059669; color:#fff; padding:14px; border-radius:12px; border:none; cursor:pointer; font-weight:700; font-size:16px;">
                        <i class="fas fa-check-circle mr-2"></i> Cobrar
                    </button>
                </div>
            </div>
        </div>

        {{-- ===== MODAL ABRIR TURNO ===== --}}
        <div id="shift-open-modal" style="display:none; position:fixed; inset:0; z-index:60; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
            <div style="background:#fff; border-radius:16px; padding:24px; max-width:400px; width:90%; margin:auto;">
                <h3 style="font-size:20px; font-weight:700; margin:0 0 16px; color:#1f2937;">
                    <i class="fas fa-cash-register text-green-600 mr-2"></i>Abrir Turno
                </h3>
                <div style="margin-bottom:16px;">
                    <label style="display:block; font-size:14px; color:#374151; margin-bottom:6px;">Fondo Inicial en Caja</label>
                    <input type="number" id="shift-initial-cash" step="0.01" min="0" value="0" placeholder="0.00"
                           style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:12px; font-size:18px; box-sizing:border-box;">
                </div>
                <div style="display:flex; gap:12px;">
                    <button onclick="closeShiftModal()" style="flex:1; background:#f3f4f6; color:#374151; padding:12px; border-radius:8px; border:none; cursor:pointer; font-weight:600;">
                        Cancelar
                    </button>
                    <button onclick="confirmOpenShift()" style="flex:1; background:#059669; color:#fff; padding:12px; border-radius:8px; border:none; cursor:pointer; font-weight:600;">
                        <i class="fas fa-play mr-1"></i> Abrir
                    </button>
                </div>
            </div>
        </div>

        {{-- ===== MODAL CERRAR TURNO ===== --}}
        <div id="shift-close-modal" style="display:none; position:fixed; inset:0; z-index:60; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
            <div style="background:#fff; border-radius:16px; padding:24px; max-width:500px; width:90%; margin:auto; max-height:90vh; overflow-y:auto;">
                <h3 style="font-size:20px; font-weight:700; margin:0 0 16px; color:#1f2937;">
                    <i class="fas fa-calculator text-blue-600 mr-2"></i>Cerrar Turno
                </h3>

                {{-- Resumen del turno --}}
                <div id="shift-summary" style="background:#f9fafb; border-radius:12px; padding:16px; margin-bottom:16px;">
                    <p style="text-align:center; color:#6b7280;">Cargando resumen...</p>
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block; font-size:14px; color:#374151; margin-bottom:6px;">Notas del Cierre (opcional)</label>
                    <textarea id="shift-close-notes" rows="2" placeholder="Observaciones..."
                              style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-size:14px; box-sizing:border-box;"></textarea>
                </div>
                <div style="display:flex; gap:12px;">
                    <button onclick="closeShiftModal()" style="flex:1; background:#f3f4f6; color:#374151; padding:12px; border-radius:8px; border:none; cursor:pointer; font-weight:600;">
                        Cancelar
                    </button>
                    <button id="confirm-close-shift-btn" onclick="confirmCloseShift()" style="flex:1; background:#dc2626; color:#fff; padding:12px; border-radius:8px; border:none; cursor:pointer; font-weight:600;">
                        <i class="fas fa-stop mr-1"></i> Cerrar Turno
                    </button>
                </div>
            </div>
        </div>

        {{-- ===== MODAL RESUMEN POST-CIERRE ===== --}}
        <div id="shift-closed-modal" style="display:none; position:fixed; inset:0; z-index:60; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
            <div style="background:#fff; border-radius:16px; padding:24px; max-width:500px; width:90%; margin:auto; max-height:90vh; overflow-y:auto;">
                <div style="text-align:center; margin-bottom:16px;">
                    <div style="width:60px; height:60px; background:#d1fae5; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 12px;">
                        <i class="fas fa-check text-green-600 text-2xl"></i>
                    </div>
                    <h3 style="font-size:20px; font-weight:700; color:#1f2937; margin:0;">Turno Cerrado</h3>
                </div>
                <div id="shift-closed-summary" style="background:#f9fafb; border-radius:12px; padding:16px; margin-bottom:16px;">
                </div>
                <button onclick="closeShiftModal()" style="width:100%; background:#3b82f6; color:#fff; padding:12px; border-radius:8px; border:none; cursor:pointer; font-weight:600;">
                    Aceptar
                </button>
            </div>
        </div>

        {{-- ===== MODAL NOTAS POR ITEM ===== --}}
        <div id="item-notes-modal" style="display:none; position:fixed; inset:0; z-index:60; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
            <div style="background:#fff; border-radius:16px; padding:24px; max-width:400px; width:90%;">
                <h3 style="font-size:18px; font-weight:700; margin:0 0 16px;">Notas para el item</h3>
                <textarea id="item-notes-input" rows="3" placeholder="Ej: Sin cebolla, término medio..."
                          style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-size:14px; box-sizing:border-box;"></textarea>
                <div style="display:flex; gap:12px; margin-top:16px;">
                    <button onclick="closeItemNotesModal()" style="flex:1; background:#f3f4f6; padding:10px; border-radius:8px; border:none; cursor:pointer; font-weight:600;">
                        Cancelar
                    </button>
                    <button onclick="saveItemNotes()" style="flex:1; background:#3b82f6; color:#fff; padding:10px; border-radius:8px; border:none; cursor:pointer; font-weight:600;">
                        Guardar
                    </button>
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

    // Estado del modo edicion
    let editingOrderId = null;
    let editingOrderNumber = null;
    let originalItems = [];

    // =============================================
    // INICIALIZACIÓN
    // =============================================
    document.addEventListener('DOMContentLoaded', function() {
        // Proteccion: limpiar items huerfanos de edicion previa
        if (cart.some(item => item.isOriginal) && !editingOrderId) {
            cart = [];
            localStorage.removeItem('quickrestaurant_cart');
        }

        setupEventListeners();
        updateCartDisplay();
        updateTime();
        setInterval(updateTime, 1000);
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
        if (editingOrderId) {
            const existingNewItem = cart.find(item => item.id === id && !item.isOriginal);
            if (existingNewItem) {
                existingNewItem.quantity += 1;
                existingNewItem.total = existingNewItem.quantity * existingNewItem.price;
            } else {
                cart.push({ id, name, price: parseFloat(price), quantity: 1, total: parseFloat(price), isOriginal: false, special_instructions: '' });
            }
        } else {
            const existingItemIndex = cart.findIndex(item => item.id === id);
            if (existingItemIndex !== -1) {
                cart[existingItemIndex].quantity += 1;
                cart[existingItemIndex].total = cart[existingItemIndex].quantity * cart[existingItemIndex].price;
            } else {
                cart.push({ id, name, price: parseFloat(price), quantity: 1, total: parseFloat(price), special_instructions: '' });
            }
        }
        saveCart();
        updateCartDisplay();
        if (buttonElement) animateAddButton(buttonElement);
        showNotification(`${name} agregado al carrito`, 'success');
    }

    function updateQuantity(id, change) {
        let itemIndex;
        if (typeof id === 'string' && id.startsWith('orig-')) {
            const oId = parseInt(id.replace('orig-', ''));
            itemIndex = cart.findIndex(item => item.isOriginal && item.orderItemId === oId);
        } else {
            itemIndex = cart.findIndex(item => !item.isOriginal && item.id === id);
            if (itemIndex === -1) itemIndex = cart.findIndex(item => item.id === id);
        }

        if (itemIndex !== -1) {
            cart[itemIndex].quantity += change;
            if (cart[itemIndex].quantity <= 0) {
                cart.splice(itemIndex, 1);
                showNotification('Item eliminado', 'info');
            } else {
                cart[itemIndex].total = cart[itemIndex].quantity * cart[itemIndex].price;
            }
            
            saveCart();
            updateCartDisplay();
        }
    }

    function removeFromCart(id) {
        let itemIndex;
        if (typeof id === 'string' && id.startsWith('orig-')) {
            const oId = parseInt(id.replace('orig-', ''));
            itemIndex = cart.findIndex(item => item.isOriginal && item.orderItemId === oId);
        } else {
            itemIndex = cart.findIndex(item => !item.isOriginal && item.id === id);
            if (itemIndex === -1) itemIndex = cart.findIndex(item => item.id === id);
        }
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
            const itemKey = item.isOriginal ? `'orig-${item.orderItemId}'` : item.id;
            const bgClass = item.isOriginal ? 'bg-gray-50' : '';
            const badge = item.isOriginal
                ? '<span class="text-xs bg-gray-200 text-gray-600 px-1.5 py-0.5 rounded ml-2"><i class="fas fa-check text-[10px] mr-0.5"></i>Existente</span>'
                : (editingOrderId ? '<span class="text-xs bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded ml-2">Nuevo</span>' : '');
            const notesIndicator = item.special_instructions
                ? `<div class="text-xs text-gray-500 italic mt-1"><i class="fas fa-sticky-note text-blue-500 mr-1"></i>${item.special_instructions}</div>`
                : '';

            cartHTML += `
                <div class="cart-item flex justify-between items-center border-b pb-3 ${bgClass}">
                    <div class="flex-1">
                        <div class="font-semibold text-gray-800">${item.name}${badge}</div>
                        <div class="text-sm text-gray-600">$${item.price.toFixed(2)} c/u</div>
                        ${notesIndicator}
                        <div class="flex items-center mt-2">
                            <button onclick="updateQuantity(${itemKey}, -1)"
                                    class="w-7 h-7 bg-gray-200 rounded flex items-center justify-center hover:bg-gray-300 transition">
                                <i class="fas fa-minus text-xs"></i>
                            </button>
                            <span class="mx-3 font-medium text-center min-w-[20px]">${item.quantity}</span>
                            <button onclick="updateQuantity(${itemKey}, 1)"
                                    class="w-7 h-7 bg-gray-200 rounded flex items-center justify-center hover:bg-gray-300 transition">
                                <i class="fas fa-plus text-xs"></i>
                            </button>
                            <button onclick="openItemNotesModal(${itemKey})"
                                    class="ml-2 text-blue-500 hover:text-blue-700 transition"
                                    title="Agregar notas">
                                <i class="fas fa-sticky-note"></i>
                            </button>
                            <button onclick="removeFromCart(${itemKey})"
                                    class="ml-2 text-red-500 hover:text-red-700 transition">
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

    function updateTotals(itemsTotal) {
        // Los precios ya incluyen IVA, calculamos el desglose
        const total = itemsTotal; // Total a cobrar = suma de precios
        const subtotalSinIva = total / 1.16; // Precio sin IVA
        const ivaIncluido = total - subtotalSinIva; // IVA incluido en el precio

        const subtotalEl = document.getElementById('subtotal');
        const taxEl = document.getElementById('tax');
        const totalEl = document.getElementById('total');
        const totalText = document.getElementById('total-text');

        if (totalEl) totalEl.textContent = `$${total.toFixed(2)}`;
        if (subtotalEl) subtotalEl.textContent = `$${subtotalSinIva.toFixed(2)}`;
        if (taxEl) taxEl.textContent = `$${ivaIncluido.toFixed(2)}`;
        if (totalText) totalText.textContent = ` ($${total.toFixed(2)})`;
    }

    // =============================================
    // CONFIRMACIÓN DE ORDEN (CORREGIDA)
    // =============================================
    async function handleConfirmOrder() {
        // Si estamos en modo edición, delegar a handleSaveEdits
        if (editingOrderId) {
            return handleSaveEdits();
        }

        console.log('📝 Confirmando orden...');

        // Validar turno abierto
        if (!currentShift) {
            showNotification('Debes abrir un turno de caja primero', 'error');
            openOpenShiftModal();
            return;
        }

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
                quantity: item.quantity,
                special_instructions: item.special_instructions || null
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
    // GESTIÓN DE NOTAS POR ITEM
    // =============================================
    let editingItemKey = null;

    function openItemNotesModal(itemKey) {
        editingItemKey = itemKey;
        const item = cart.find(i => {
            if (typeof itemKey === 'string' && itemKey.startsWith('orig-')) {
                const oId = parseInt(itemKey.replace(/'/g, '').replace('orig-', ''));
                return i.isOriginal && i.orderItemId === oId;
            }
            return i.id === itemKey;
        });

        document.getElementById('item-notes-input').value = item?.special_instructions || '';
        document.getElementById('item-notes-modal').style.display = 'flex';
    }

    function closeItemNotesModal() {
        document.getElementById('item-notes-modal').style.display = 'none';
        editingItemKey = null;
    }

    function saveItemNotes() {
        const notes = document.getElementById('item-notes-input').value;
        const itemIndex = cart.findIndex(i => {
            if (typeof editingItemKey === 'string' && editingItemKey.startsWith('orig-')) {
                const oId = parseInt(editingItemKey.replace(/'/g, '').replace('orig-', ''));
                return i.isOriginal && i.orderItemId === oId;
            }
            return i.id === editingItemKey;
        });

        if (itemIndex !== -1) {
            cart[itemIndex].special_instructions = notes;
            saveCart();
            updateCartDisplay();
        }

        closeItemNotesModal();
        showNotification('Notas guardadas', 'success');
    }

    // =============================================
    // EXPORTAR FUNCIONES GLOBALES
    // =============================================
    window.addToCart = addToCart;
    window.updateQuantity = updateQuantity;
    window.removeFromCart = removeFromCart;
    window.clearCart = clearCart;
    window.handleConfirmOrder = handleConfirmOrder;
    window.openItemNotesModal = openItemNotesModal;
    window.closeItemNotesModal = closeItemNotesModal;
    window.saveItemNotes = saveItemNotes;

    // =============================================
    // SISTEMA DE ÓRDENES ACTIVAS Y COBRO
    // =============================================

    let ordersDrawerOpen = false;
    let orderDetailOpen = false;
    let activeOrders = [];
    let selectedOrder = null;
    let selectedPaymentMethod = 'efectivo';

    // =============================================
    // ICONOS POR TIPO DE ORDEN
    // =============================================
    const orderTypeIcons = {
        'didi': { icon: 'fa-motorcycle', color: '#ff6600', label: 'Didi Food' },
        'uber': { icon: 'fa-car', color: '#000000', label: 'Uber Eats' },
        'rappi': { icon: 'fa-bicycle', color: '#ff441f', label: 'Rappi' },
        'mesa': { icon: 'fa-utensils', color: '#3b82f6', label: 'Mesa' },
        'to go': { icon: 'fa-shopping-bag', color: '#059669', label: 'To Go' },
        'default': { icon: 'fa-receipt', color: '#6b7280', label: 'Orden' }
    };

    function getOrderTypeIcon(tableNumber) {
        const lower = (tableNumber || '').toLowerCase();
        if (lower.includes('didi')) return orderTypeIcons['didi'];
        if (lower.includes('uber')) return orderTypeIcons['uber'];
        if (lower.includes('rappi')) return orderTypeIcons['rappi'];
        if (lower.includes('mesa') || /^\d+$/.test(tableNumber)) return orderTypeIcons['mesa'];
        if (lower.includes('to go') || lower.includes('togo')) return orderTypeIcons['to go'];
        return orderTypeIcons['default'];
    }

    // =============================================
    // TOGGLE DRAWER ÓRDENES
    // =============================================
    function toggleOrdersDrawer() {
        ordersDrawerOpen = !ordersDrawerOpen;

        document.getElementById('orders-drawer').style.transform =
            ordersDrawerOpen ? 'translateY(0)' : 'translateY(100%)';

        const backdrop = document.getElementById('orders-backdrop');
        backdrop.style.opacity = ordersDrawerOpen ? '1' : '0';
        backdrop.style.pointerEvents = ordersDrawerOpen ? 'auto' : 'none';

        if (ordersDrawerOpen) {
            loadActiveOrders();
        }
    }

    // =============================================
    // CARGAR ÓRDENES ACTIVAS
    // =============================================
    async function loadActiveOrders() {
        const container = document.getElementById('orders-list');
        container.innerHTML = '<p class="text-center text-gray-400 py-8"><i class="fas fa-spinner fa-spin mr-2"></i>Cargando...</p>';

        try {
            const response = await fetch('/api/orders/active', {
                headers: { 'Accept': 'application/json' }
            });

            const data = await response.json();

            if (!response.ok) throw new Error(data.message || 'Error al cargar órdenes');

            activeOrders = data.orders;
            renderOrdersList();
            updateOrdersBar();

        } catch (error) {
            console.error('Error cargando órdenes:', error);
            container.innerHTML = `<p class="text-center text-red-500 py-8"><i class="fas fa-exclamation-circle mr-2"></i>${error.message}</p>`;
        }
    }

    // =============================================
    // RENDERIZAR LISTA DE ÓRDENES
    // =============================================
    function renderOrdersList() {
        const container = document.getElementById('orders-list');

        if (activeOrders.length === 0) {
            container.innerHTML = `
                <div class="text-center py-12 text-gray-400">
                    <i class="fas fa-check-circle text-5xl text-gray-300 mb-4" style="display:block;"></i>
                    <p class="font-medium" style="margin:0;">No hay órdenes pendientes</p>
                    <p class="text-sm mt-1" style="margin-top:4px;">Las órdenes activas aparecerán aquí</p>
                </div>
            `;
            return;
        }

        container.innerHTML = activeOrders.map(order => {
            const typeInfo = getOrderTypeIcon(order.table_number);
            const statusColors = {
                'pending': { bg: '#fef3c7', text: '#92400e', label: 'Pendiente' },
                'preparing': { bg: '#dbeafe', text: '#1e40af', label: 'Preparando' },
                'ready': { bg: '#d1fae5', text: '#065f46', label: 'Listo' }
            };
            const status = statusColors[order.status] || statusColors['pending'];

            return `
                <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:12px; margin-bottom:10px; transition:all 0.2s;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:40px; height:40px; background:${typeInfo.color}15; border-radius:10px; display:flex; align-items:center; justify-content:center;">
                                <i class="fas ${typeInfo.icon}" style="color:${typeInfo.color};"></i>
                            </div>
                            <div>
                                <p style="font-weight:700; color:#1f2937; margin:0;">${order.order_number}</p>
                                <p style="font-size:13px; color:#6b7280; margin:2px 0 0;">${order.table_number || 'Sin mesa'}</p>
                            </div>
                        </div>
                        <span style="background:${status.bg}; color:${status.text}; font-size:12px; font-weight:600; padding:4px 10px; border-radius:20px;">
                            ${status.label}
                        </span>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid #f3f4f6; padding-top:8px;">
                        <span style="font-size:14px; color:#6b7280;">
                            ${order.customer_name || 'Sin nombre'}
                        </span>
                        <div style="text-align:right;">
                            <p style="font-weight:700; color:#1f2937; margin:0;">$${parseFloat(order.total).toFixed(2)}</p>
                            ${order.amount_remaining > 0 ? `<p style="font-size:12px; color:#dc2626; margin:2px 0 0;">Debe: $${parseFloat(order.amount_remaining).toFixed(2)}</p>` : '<p style="font-size:12px; color:#059669; margin:2px 0 0;"><i class="fas fa-check mr-1"></i>Pagado</p>'}
                        </div>
                    </div>
                    <div style="display:flex; gap:8px; margin-top:8px; border-top:1px solid #f3f4f6; padding-top:8px;" onclick="event.stopPropagation()">
                        <button onclick="editOrder(${order.id})"
                                style="flex:1; padding:6px 0; font-size:13px; font-weight:600; color:#92400e; background:#fef3c7; border:1px solid #f59e0b; border-radius:6px; cursor:pointer;">
                            <i class="fas fa-edit mr-1"></i> Editar
                        </button>
                        <button onclick="openOrderDetail(${order.id})"
                                style="flex:1; padding:6px 0; font-size:13px; font-weight:600; color:#065f46; background:#d1fae5; border:1px solid #10b981; border-radius:6px; cursor:pointer;">
                            <i class="fas fa-cash-register mr-1"></i> Cobrar
                        </button>
                    </div>
                </div>
            `;
        }).join('');
    }

    // =============================================
    // ACTUALIZAR BARRA DE ÓRDENES
    // =============================================
    function updateOrdersBar() {
        const count = activeOrders.filter(o => o.amount_remaining > 0).length;
        document.getElementById('orders-count').textContent = count;
        document.getElementById('orders-label').innerHTML = count > 0
            ? `<i class="fas fa-receipt mr-2"></i>${count} Orden${count > 1 ? 'es' : ''} por cobrar`
            : '<i class="fas fa-check-circle mr-2"></i>Sin órdenes pendientes';
    }

    // =============================================
    // ABRIR DETALLE DE ORDEN
    // =============================================
    function openOrderDetail(orderId) {
        selectedOrder = activeOrders.find(o => o.id === orderId);
        if (!selectedOrder) return;

        orderDetailOpen = true;

        // Llenar datos
        document.getElementById('detail-order-number').textContent = selectedOrder.order_number;

        const typeInfo = getOrderTypeIcon(selectedOrder.table_number);
        document.getElementById('detail-table-info').innerHTML =
            `<i class="fas ${typeInfo.icon} mr-1" style="color:${typeInfo.color}"></i> ${selectedOrder.table_number || 'Sin mesa'} - ${selectedOrder.customer_name || 'Sin nombre'}`;

        // Renderizar items
        const itemsContainer = document.getElementById('detail-items');
        itemsContainer.innerHTML = (selectedOrder.items || []).map(item => `
            <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f3f4f6;">
                <div>
                    <span style="font-weight:500;">${item.quantity}x</span>
                    <span style="color:#374151;">${item.dish?.name || 'Item'}</span>
                </div>
                <span style="font-weight:600;">$${parseFloat(item.total_price).toFixed(2)}</span>
            </div>
        `).join('');

        // Actualizar resumen de pagos
        document.getElementById('detail-total').textContent = `$${parseFloat(selectedOrder.total).toFixed(2)}`;
        document.getElementById('detail-paid').textContent = `$${parseFloat(selectedOrder.amount_paid || 0).toFixed(2)}`;
        document.getElementById('detail-remaining').textContent = `$${parseFloat(selectedOrder.amount_remaining).toFixed(2)}`;

        // Pre-llenar monto restante
        document.getElementById('payment-amount').value = selectedOrder.amount_remaining.toFixed(2);
        document.getElementById('payment-tip').value = '0';

        // Reset método de pago
        selectedPaymentMethod = 'efectivo';
        document.querySelectorAll('.payment-method-btn').forEach(b => {
            if (b.dataset.method === 'efectivo') {
                b.style.border = '2px solid #059669';
                b.style.background = '#ecfdf5';
            } else {
                b.style.border = '2px solid #e5e7eb';
                b.style.background = '#fff';
            }
        });

        // Mostrar/ocultar formulario de pago
        const paymentForm = document.getElementById('payment-form');
        if (selectedOrder.amount_remaining <= 0) {
            paymentForm.style.display = 'none';
        } else {
            paymentForm.style.display = 'block';
        }

        // Abrir drawer
        document.getElementById('order-detail-drawer').style.transform = 'translateY(0)';
    }

    // =============================================
    // CERRAR DETALLE DE ORDEN
    // =============================================
    function closeOrderDetail() {
        orderDetailOpen = false;
        document.getElementById('order-detail-drawer').style.transform = 'translateY(100%)';
        selectedOrder = null;
    }

    // =============================================
    // SELECCIÓN DE MÉTODO DE PAGO
    // =============================================
    document.addEventListener('click', function(e) {
        if (e.target.closest('.payment-method-btn')) {
            const btn = e.target.closest('.payment-method-btn');
            document.querySelectorAll('.payment-method-btn').forEach(b => {
                b.style.border = '2px solid #e5e7eb';
                b.style.background = '#fff';
            });
            btn.style.border = '2px solid #059669';
            btn.style.background = '#ecfdf5';
            selectedPaymentMethod = btn.dataset.method;
        }
    });

    // =============================================
    // LLENAR MONTO TOTAL
    // =============================================
    function setFullPayment() {
        if (selectedOrder) {
            document.getElementById('payment-amount').value = selectedOrder.amount_remaining.toFixed(2);
        }
    }

    // =============================================
    // PROCESAR PAGO
    // =============================================
    async function processPayment() {
        if (!selectedOrder) return;

        const amount = parseFloat(document.getElementById('payment-amount').value);
        const tip = parseFloat(document.getElementById('payment-tip').value) || 0;

        if (!amount || amount <= 0) {
            showNotification('Ingresa un monto válido', 'error');
            return;
        }

        const btn = document.getElementById('process-payment-btn');
        const origHTML = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Procesando...';
        btn.disabled = true;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            const response = await fetch(`/api/orders/${selectedOrder.id}/payments`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    method: selectedPaymentMethod,
                    amount: amount,
                    tip: tip
                })
            });

            const data = await response.json();

            if (!response.ok) throw new Error(data.message || 'Error al procesar pago');

            showNotification(`Pago de $${amount.toFixed(2)} registrado exitosamente`, 'success');

            // Actualizar orden en la lista
            const orderIndex = activeOrders.findIndex(o => o.id === selectedOrder.id);
            if (orderIndex !== -1) {
                activeOrders[orderIndex].amount_paid = data.order.amount_paid;
                activeOrders[orderIndex].amount_remaining = data.order.amount_remaining;
                activeOrders[orderIndex].payment_status = data.order.payment_status;

                // Si está completamente pagada, remover de la lista
                if (data.order.payment_status === 'paid') {
                    activeOrders.splice(orderIndex, 1);
                }
            }

            closeOrderDetail();
            renderOrdersList();
            updateOrdersBar();

        } catch (error) {
            console.error('Error procesando pago:', error);
            showNotification(error.message, 'error');
        } finally {
            btn.innerHTML = origHTML;
            btn.disabled = false;
        }
    }

    // =============================================
    // AUTO-REFRESH DE ÓRDENES
    // =============================================
    setInterval(() => {
        if (ordersDrawerOpen && !orderDetailOpen) {
            loadActiveOrders();
        }
    }, 30000); // Cada 30 segundos

    // Cargar conteo inicial al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        // Cargar órdenes en background para mostrar contador
        fetch('/api/orders/active', { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(data => {
                activeOrders = data.orders || [];
                updateOrdersBar();
            })
            .catch(err => console.error('Error cargando órdenes iniciales:', err));
    });

    // =============================================
    // MODO EDICIÓN DE ÓRDENES
    // =============================================
    async function editOrder(orderId) {
        const order = activeOrders.find(o => o.id === orderId);
        if (!order) {
            showNotification('Orden no encontrada', 'error');
            return;
        }

        // Cargar items en el carrito como originales
        cart = (order.items || []).map(item => ({
            id: item.dish_id || item.dish?.id,
            orderItemId: item.id,
            name: item.dish?.name || 'Item',
            price: parseFloat(item.unit_price),
            quantity: item.quantity,
            total: parseFloat(item.total_price),
            special_instructions: item.special_instructions || '',
            isOriginal: true
        }));

        // Guardar snapshot original para diff
        originalItems = cart.map(item => ({ ...item }));

        editingOrderId = order.id;
        editingOrderNumber = order.order_number;

        // Cerrar drawer de órdenes
        toggleOrdersDrawer();

        // Llenar mesa y cliente
        const tableEl = document.getElementById('table-type');
        const customerEl = document.getElementById('customer-name');
        if (tableEl) { tableEl.value = order.table_number || ''; tableEl.disabled = true; }
        if (customerEl) { customerEl.value = order.customer_name || ''; customerEl.disabled = true; }

        saveCart();
        updateCartDisplay();
        updateEditModeUI(true);

        showNotification(`Editando orden ${order.order_number}`, 'info');
    }

    function updateEditModeUI(isEditing) {
        const confirmBtn = document.getElementById('confirm-order');
        const clearBtn = document.getElementById('clear-cart');
        const cartTitle = document.querySelector('#cart-items')?.closest('.bg-white')?.querySelector('h3, .font-bold');

        if (isEditing) {
            // Banner de edición
            let banner = document.getElementById('edit-mode-banner');
            if (!banner) {
                banner = document.createElement('div');
                banner.id = 'edit-mode-banner';
                banner.style.cssText = 'background:#fef3c7; border:1px solid #f59e0b; border-radius:8px; padding:8px 12px; margin-bottom:8px; display:flex; align-items:center; gap:8px;';
                banner.innerHTML = `<i class="fas fa-edit text-amber-600"></i><span style="font-size:13px; font-weight:600; color:#92400e;">Editando: ${editingOrderNumber}</span>`;
                const cartContainer = document.getElementById('cart-items');
                if (cartContainer) cartContainer.parentElement.insertBefore(banner, cartContainer);
            }

            if (confirmBtn) {
                confirmBtn.innerHTML = '<i class="fas fa-save mr-2"></i> Guardar Cambios';
                confirmBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
                confirmBtn.classList.add('bg-amber-600', 'hover:bg-amber-700');
            }
            if (clearBtn) {
                clearBtn.innerHTML = '<i class="fas fa-times mr-2"></i> Cancelar';
                clearBtn.removeEventListener('click', clearCart);
                clearBtn.addEventListener('click', cancelEditMode);
            }
        } else {
            // Remover banner
            const banner = document.getElementById('edit-mode-banner');
            if (banner) banner.remove();

            if (confirmBtn) {
                confirmBtn.innerHTML = '<i class="fas fa-check mr-2"></i> Confirmar Orden';
                confirmBtn.classList.remove('bg-amber-600', 'hover:bg-amber-700');
                confirmBtn.classList.add('bg-green-600', 'hover:bg-green-700');
            }
            if (clearBtn) {
                clearBtn.innerHTML = '<i class="fas fa-trash mr-2"></i> Limpiar Todo';
                clearBtn.removeEventListener('click', cancelEditMode);
                clearBtn.addEventListener('click', clearCart);
            }

            // Rehabilitar campos
            const tableEl = document.getElementById('table-type');
            const customerEl = document.getElementById('customer-name');
            if (tableEl) tableEl.disabled = false;
            if (customerEl) customerEl.disabled = false;
        }
    }

    function cancelEditMode() {
        if (!confirm('¿Cancelar la edición? Los cambios no guardados se perderán.')) return;

        editingOrderId = null;
        editingOrderNumber = null;
        originalItems = [];
        cart = [];
        localStorage.removeItem('quickrestaurant_cart');

        updateEditModeUI(false);
        updateCartDisplay();

        // Limpiar campos
        const tableEl = document.getElementById('table-type');
        const customerEl = document.getElementById('customer-name');
        if (tableEl) { tableEl.value = ''; tableEl.disabled = false; }
        if (customerEl) { customerEl.value = ''; customerEl.disabled = false; }

        showNotification('Edición cancelada', 'info');
    }

    async function handleSaveEdits() {
        const confirmBtn = document.getElementById('confirm-order');
        const origHTML = confirmBtn.innerHTML;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Guardando...';
        confirmBtn.disabled = true;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken };

        try {
            // 1. Items eliminados (estaban en original, ya no están en cart)
            for (const orig of originalItems) {
                const stillExists = cart.find(c => c.isOriginal && c.orderItemId === orig.orderItemId);
                if (!stillExists) {
                    const res = await fetch(`/api/orders/${editingOrderId}/items/${orig.orderItemId}`, {
                        method: 'DELETE', headers, body: JSON.stringify({ reason: 'Eliminado desde POS' })
                    });
                    if (!res.ok) { const d = await res.json(); throw new Error(d.message || 'Error eliminando item'); }
                }
            }

            // 2. Items con cantidad cambiada
            for (const item of cart.filter(c => c.isOriginal)) {
                const orig = originalItems.find(o => o.orderItemId === item.orderItemId);
                if (orig && orig.quantity !== item.quantity) {
                    const res = await fetch(`/api/orders/${editingOrderId}/items/${item.orderItemId}`, {
                        method: 'PUT', headers, body: JSON.stringify({ quantity: item.quantity })
                    });
                    if (!res.ok) { const d = await res.json(); throw new Error(d.message || 'Error actualizando item'); }
                }
            }

            // 3. Items nuevos
            const newItems = cart.filter(c => !c.isOriginal);
            if (newItems.length > 0) {
                const res = await fetch(`/api/orders/${editingOrderId}/items`, {
                    method: 'POST', headers,
                    body: JSON.stringify({ items: newItems.map(i => ({ id: i.id, quantity: i.quantity, price: i.price })) })
                });
                if (!res.ok) { const d = await res.json(); throw new Error(d.message || 'Error agregando items'); }
            }

            showNotification(`Orden ${editingOrderNumber} actualizada exitosamente`, 'success');

            // Salir del modo edición
            editingOrderId = null;
            editingOrderNumber = null;
            originalItems = [];
            cart = [];
            localStorage.removeItem('quickrestaurant_cart');

            updateEditModeUI(false);
            updateCartDisplay();

            const tableEl = document.getElementById('table-type');
            const customerEl = document.getElementById('customer-name');
            if (tableEl) { tableEl.value = ''; tableEl.disabled = false; }
            if (customerEl) { customerEl.value = ''; customerEl.disabled = false; }

        } catch (error) {
            console.error('Error guardando edición:', error);
            showNotification(error.message || 'Error al guardar cambios', 'error');
        } finally {
            confirmBtn.innerHTML = origHTML;
            confirmBtn.disabled = cart.length === 0;
        }
    }

    async function printSaleTicket() {
        if (!selectedOrder) {
            showNotification('No hay orden seleccionada', 'error');
            return;
        }

        try {
            const response = await fetch(`/api/orders/${selectedOrder.id}/print-ticket`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });

            const data = await response.json();

            if (data.success) {
                showNotification('✅ Ticket impreso correctamente', 'success');
            } else {
                showNotification('❌ ' + (data.message || 'Error al imprimir'), 'error');
            }
        } catch (error) {
            console.error('Error printing ticket:', error);
            showNotification('❌ Error de conexión al imprimir', 'error');
        }
    }

    // =============================================
    // EXPORTAR FUNCIONES DE ÓRDENES
    // =============================================
    window.toggleOrdersDrawer = toggleOrdersDrawer;
    window.openOrderDetail = openOrderDetail;
    window.closeOrderDetail = closeOrderDetail;
    window.setFullPayment = setFullPayment;
    window.processPayment = processPayment;
    window.editOrder = editOrder;
    window.cancelEditMode = cancelEditMode;
    window.printSaleTicket = printSaleTicket;

    // =============================================
    // SISTEMA DE TURNOS (CORTE DE CAJA)
    // =============================================

    let currentShift = null;

    // Cargar estado del turno al iniciar
    document.addEventListener('DOMContentLoaded', function() {
        loadShiftStatus();
    });

    async function loadShiftStatus() {
        try {
            const response = await fetch('/api/cash-shift/current', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();

            currentShift = data.is_open ? data.shift : null;
            renderShiftBar();
        } catch (error) {
            console.error('Error cargando estado del turno:', error);
            renderShiftBar();
        }
    }

    function renderShiftBar() {
        const container = document.getElementById('shift-status-bar');
        if (!container) return;

        if (currentShift) {
            container.innerHTML = `
                <div style="display:flex; align-items:center; gap:8px; background:#ecfdf5; border:1px solid #059669; border-radius:8px; padding:8px 12px;">
                    <div style="width:8px; height:8px; background:#059669; border-radius:50%; animation:pulse 2s infinite;"></div>
                    <div>
                        <div style="font-size:12px; color:#065f46; font-weight:600;">Turno Abierto</div>
                        <div style="font-size:11px; color:#047857;">${currentShift.opened_at} - ${currentShift.opened_by}</div>
                    </div>
                    <button onclick="openCloseShiftModal()" style="background:#dc2626; color:#fff; border:none; border-radius:6px; padding:6px 10px; font-size:12px; cursor:pointer; font-weight:500; margin-left:8px;">
                        <i class="fas fa-stop mr-1"></i>Cerrar
                    </button>
                </div>
            `;
        } else {
            container.innerHTML = `
                <button onclick="openOpenShiftModal()" style="display:flex; align-items:center; gap:8px; background:#fef3c7; border:1px solid #f59e0b; border-radius:8px; padding:10px 14px; cursor:pointer; font-weight:500; color:#92400e;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Sin turno abierto</span>
                    <span style="background:#059669; color:#fff; padding:4px 8px; border-radius:4px; font-size:12px;">Abrir</span>
                </button>
            `;
        }
    }

    function openOpenShiftModal() {
        document.getElementById('shift-initial-cash').value = '0';
        document.getElementById('shift-open-modal').style.display = 'flex';
    }

    async function openCloseShiftModal() {
        const modal = document.getElementById('shift-close-modal');
        const summaryDiv = document.getElementById('shift-summary');

        modal.style.display = 'flex';
        summaryDiv.innerHTML = '<p style="text-align:center; color:#6b7280;"><i class="fas fa-spinner fa-spin mr-2"></i>Cargando resumen...</p>';

        try {
            const response = await fetch('/api/cash-shift/current', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();

            if (data.is_open && data.shift) {
                const s = data.shift;
                summaryDiv.innerHTML = `
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div>
                            <div style="font-size:12px; color:#6b7280;">Abierto</div>
                            <div style="font-weight:600;">${s.opened_at} por ${s.opened_by}</div>
                        </div>
                        <div>
                            <div style="font-size:12px; color:#6b7280;">Duración</div>
                            <div style="font-weight:600;">${s.duration}</div>
                        </div>
                        <div>
                            <div style="font-size:12px; color:#6b7280;">Fondo Inicial</div>
                            <div style="font-weight:600;">$${parseFloat(s.initial_cash).toFixed(2)}</div>
                        </div>
                        <div>
                            <div style="font-size:12px; color:#6b7280;">Órdenes Cobradas</div>
                            <div style="font-weight:600;">${s.orders_count}</div>
                        </div>
                    </div>
                    <div style="border-top:1px solid #e5e7eb; margin-top:12px; padding-top:12px;">
                        <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">Ventas del Turno</div>
                        <div style="font-size:24px; font-weight:700; color:#059669;">$${parseFloat(s.total_sales).toFixed(2)}</div>
                    </div>
                `;
            }
        } catch (error) {
            summaryDiv.innerHTML = '<p style="text-align:center; color:#dc2626;">Error al cargar resumen</p>';
        }
    }

    function closeShiftModal() {
        document.getElementById('shift-open-modal').style.display = 'none';
        document.getElementById('shift-close-modal').style.display = 'none';
        document.getElementById('shift-closed-modal').style.display = 'none';
    }

    async function confirmOpenShift() {
        const initialCash = parseFloat(document.getElementById('shift-initial-cash').value) || 0;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        try {
            const response = await fetch('/api/cash-shift/open', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ initial_cash: initialCash })
            });

            const data = await response.json();

            if (data.success) {
                currentShift = data.shift;
                renderShiftBar();
                closeShiftModal();
                showNotification('Turno abierto exitosamente', 'success');
            } else {
                showNotification(data.message || 'Error al abrir turno', 'error');
            }
        } catch (error) {
            console.error('Error abriendo turno:', error);
            showNotification('Error al abrir turno', 'error');
        }
    }

    async function confirmCloseShift() {
        const notes = document.getElementById('shift-close-notes').value;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const btn = document.getElementById('confirm-close-shift-btn');

        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Cerrando...';
        btn.disabled = true;

        try {
            const response = await fetch('/api/cash-shift/close', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ notes })
            });

            const data = await response.json();

            if (data.success) {
                currentShift = null;
                renderShiftBar();
                closeShiftModal();

                // Mostrar resumen final
                showClosedSummary(data.summary);
            } else {
                showNotification(data.message || 'Error al cerrar turno', 'error');
            }
        } catch (error) {
            console.error('Error cerrando turno:', error);
            showNotification('Error al cerrar turno', 'error');
        } finally {
            btn.innerHTML = '<i class="fas fa-stop mr-1"></i> Cerrar Turno';
            btn.disabled = false;
        }
    }

    function showClosedSummary(summary) {
        const modal = document.getElementById('shift-closed-modal');
        const summaryDiv = document.getElementById('shift-closed-summary');

        const efectivo = summary.sales_by_method?.efectivo || 0;
        const tarjeta = summary.sales_by_method?.tarjeta || 0;
        const transferencia = summary.sales_by_method?.transferencia || 0;

        summaryDiv.innerHTML = `
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                <div>
                    <div style="font-size:12px; color:#6b7280;">Apertura</div>
                    <div style="font-weight:500;">${summary.opened_at}</div>
                </div>
                <div>
                    <div style="font-size:12px; color:#6b7280;">Cierre</div>
                    <div style="font-weight:500;">${summary.closed_at}</div>
                </div>
                <div>
                    <div style="font-size:12px; color:#6b7280;">Duración</div>
                    <div style="font-weight:500;">${summary.duration}</div>
                </div>
                <div>
                    <div style="font-size:12px; color:#6b7280;">Órdenes</div>
                    <div style="font-weight:500;">${summary.orders_count}</div>
                </div>
            </div>

            <div style="border-top:1px solid #e5e7eb; padding-top:12px; margin-bottom:12px;">
                <div style="font-size:14px; font-weight:600; margin-bottom:8px;">Ventas por Método</div>
                <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                    <span style="color:#6b7280;"><i class="fas fa-money-bill-wave text-green-600 mr-1"></i>Efectivo</span>
                    <span style="font-weight:500;">$${parseFloat(efectivo).toFixed(2)}</span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                    <span style="color:#6b7280;"><i class="fas fa-credit-card text-blue-600 mr-1"></i>Tarjeta</span>
                    <span style="font-weight:500;">$${parseFloat(tarjeta).toFixed(2)}</span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span style="color:#6b7280;"><i class="fas fa-university text-yellow-600 mr-1"></i>Transferencia</span>
                    <span style="font-weight:500;">$${parseFloat(transferencia).toFixed(2)}</span>
                </div>
            </div>

            <div style="border-top:1px solid #e5e7eb; padding-top:12px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                    <span style="color:#6b7280;">Fondo Inicial</span>
                    <span style="font-weight:500;">$${parseFloat(summary.initial_cash).toFixed(2)}</span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                    <span style="color:#6b7280;">Total Ventas</span>
                    <span style="font-weight:600; color:#059669;">$${parseFloat(summary.total_sales).toFixed(2)}</span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                    <span style="color:#6b7280;">Propinas</span>
                    <span style="font-weight:500;">$${parseFloat(summary.total_tips).toFixed(2)}</span>
                </div>
                <div style="display:flex; justify-content:space-between; background:#ecfdf5; padding:8px; border-radius:6px;">
                    <span style="font-weight:600; color:#065f46;">Efectivo Esperado</span>
                    <span style="font-weight:700; color:#059669;">$${parseFloat(summary.expected_cash).toFixed(2)}</span>
                </div>
            </div>
        `;

        modal.style.display = 'flex';
    }

    // =============================================
    // EXPORTAR FUNCIONES DE TURNOS
    // =============================================
    window.openOpenShiftModal = openOpenShiftModal;
    window.openCloseShiftModal = openCloseShiftModal;
    window.closeShiftModal = closeShiftModal;
    window.confirmOpenShift = confirmOpenShift;
    window.confirmCloseShift = confirmCloseShift;
</script>