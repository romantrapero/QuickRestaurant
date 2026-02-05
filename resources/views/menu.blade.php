<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Menú - QuickRestaurant</title>

    @vite('resources/css/app.css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * { -webkit-tap-highlight-color: transparent; }
        body { padding-bottom: 5rem; }
        .category-pill.active { background-color: #2563eb; color: white; }
        .cart-drawer { transform: translateY(100%); transition: transform 0.3s ease; }
        .cart-drawer.open { transform: translateY(0); }
        .backdrop { opacity: 0; pointer-events: none; transition: opacity 0.3s ease; }
        .backdrop.open { opacity: 1; pointer-events: auto; }
        .dish-section { scroll-margin-top: 4.5rem; }
        .hide { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800" data-table="{{ $table }}" data-table-name="{{ $tableName }}">

    {{-- ===== HEADER ===== --}}
    <header class="bg-white shadow-sm sticky top-0 z-30">
        <div class="px-4 py-3 flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-utensils text-blue-500 mr-1"></i> QuickRestaurant
                </h1>
            </div>
            <div class="text-right">
                <span class="inline-flex items-center bg-blue-100 text-blue-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                    <i class="fas fa-map-pin mr-1"></i> {{ $tableName }}
                </span>
            </div>
        </div>

        {{-- Category pills --}}
        <div class="flex gap-2 px-4 pb-3 overflow-x-auto scrollbar-none">
            <button onclick="filterCategory('all')"
                    class="category-pill active shrink-0 px-4 py-1.5 rounded-full text-sm font-medium bg-gray-200 text-gray-700 transition" data-cat="all">
                Todos
            </button>
            @foreach($categories as $category)
                <button onclick="filterCategory({{ $category->id }})"
                        class="category-pill shrink-0 px-4 py-1.5 rounded-full text-sm font-medium bg-gray-200 text-gray-700 transition" data-cat="{{ $category->id }}">
                    {{ $category->name }}
                </button>
            @endforeach
        </div>
    </header>

    {{-- ===== MENU CONTENT ===== --}}
    <main class="px-4 py-4 space-y-6">
        @foreach($categories as $category)
            <section class="dish-section" data-category="{{ $category->id }}">
                <h2 class="text-lg font-bold text-gray-700 mb-3">
                    {{ $category->name }}
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($category->dishes as $dish)
                        <div class="bg-white rounded-xl shadow-sm overflow-hidden flex flex-col">
                            @if($dish->image_url)
                                <img src="{{ asset('storage/dishes/' . $dish->image_url) }}"
                                     alt="{{ $dish->name }}"
                                     class="w-full h-40 object-cover" loading="lazy">
                            @else
                                <div class="w-full h-32 bg-gray-100 flex items-center justify-center text-gray-300">
                                    <i class="fas fa-image fa-2x"></i>
                                </div>
                            @endif
                            <div class="p-3 flex-1 flex flex-col">
                                <h3 class="font-semibold text-gray-800">{{ $dish->name }}</h3>
                                @if($dish->description)
                                    <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $dish->description }}</p>
                                @endif
                                <div class="mt-auto pt-3 flex items-center justify-between">
                                    <span class="text-lg font-bold text-blue-600">${{ number_format($dish->sale_price, 2) }}</span>
                                    <button class="add-to-cart-btn bg-blue-500 text-white px-3 py-1.5 rounded-lg text-sm font-semibold hover:bg-blue-600 active:scale-95 transition"
                                            data-id="{{ $dish->id }}"
                                            data-name="{{ $dish->name }}"
                                            data-price="{{ $dish->sale_price }}">
                                        <i class="fas fa-plus mr-1"></i> Agregar
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach
    </main>

    {{-- ===== FLOATING CART BAR (always visible) ===== --}}
    <div id="cart-bar" onclick="toggleCart()"
         style="position:fixed; bottom:0; left:0; right:0; z-index:40; background:#2563eb; color:#fff; padding:14px 16px; display:flex; align-items:center; justify-content:space-between; cursor:pointer; box-shadow:0 -2px 10px rgba(0,0,0,0.15);">
        <div style="display:flex; align-items:center; gap:12px;">
            <span id="bar-count" style="background:#fff; color:#2563eb; font-weight:700; font-size:14px; width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center;">0</span>
            <span id="bar-label" style="font-weight:600;">Tu carrito está vacío</span>
        </div>
        <span id="bar-total" style="font-weight:700; font-size:18px;"></span>
    </div>

    {{-- ===== BACKDROP ===== --}}
    <div id="backdrop" onclick="toggleCart()"
         style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:40; opacity:0; pointer-events:none; transition:opacity 0.3s ease;"></div>

    {{-- ===== CART DRAWER ===== --}}
    <div id="cart-drawer"
         style="position:fixed; bottom:0; left:0; right:0; z-index:50; background:#fff; border-radius:16px 16px 0 0; box-shadow:0 -4px 20px rgba(0,0,0,0.15); max-height:85vh; display:flex; flex-direction:column; transform:translateY(100%); transition:transform 0.3s ease;">

        {{-- Drawer handle --}}
        <div style="display:flex; justify-content:center; padding:12px 0 4px;" onclick="toggleCart()">
            <div style="width:40px; height:4px; background:#d1d5db; border-radius:2px;"></div>
        </div>

        <div style="padding:0 16px 8px; display:flex; align-items:center; justify-content:space-between;">
            <h2 style="font-size:18px; font-weight:700; color:#1f2937;">
                <i class="fas fa-shopping-cart" style="color:#3b82f6; margin-right:6px;"></i> Mi Pedido
            </h2>
            <button onclick="clearCart()" style="font-size:14px; color:#ef4444; font-weight:500; background:none; border:none; cursor:pointer;">
                <i class="fas fa-trash-alt" style="margin-right:4px;"></i> Vaciar
            </button>
        </div>

        {{-- Cart items (scrollable) --}}
        <div id="cart-items" style="flex:1; overflow-y:auto; padding:0 16px 8px;">
            <p id="empty-msg" style="text-align:center; color:#9ca3af; padding:32px 0;">El carrito está vacío</p>
        </div>

        {{-- Customer info + totals + send --}}
        <div style="border-top:1px solid #e5e7eb; padding:12px 16px 16px; background:#fff;">
            <div style="display:flex; flex-direction:column; gap:8px; margin-bottom:12px;">
                <input type="text" id="customer-name" placeholder="Tu nombre"
                       style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:8px 12px; font-size:14px; outline:none; box-sizing:border-box;">
                <textarea id="order-notes" rows="1" placeholder="Notas (opcional)"
                          style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:8px 12px; font-size:14px; outline:none; box-sizing:border-box; resize:none;"></textarea>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:18px; font-weight:700; margin-bottom:12px;">
                <span>Total</span>
                <span id="drawer-total" style="color:#2563eb;">$0.00</span>
            </div>
            <button id="send-order" onclick="handleSendOrder()" disabled
                    style="width:100%; background:#22c55e; color:#fff; padding:14px; border-radius:12px; font-weight:700; font-size:16px; border:none; cursor:pointer; opacity:0.4;">
                <i class="fas fa-paper-plane" style="margin-right:8px;"></i> Enviar Pedido
            </button>
        </div>
    </div>

    {{-- ===== ORDER SUCCESS MODAL ===== --}}
    <div id="success-modal" style="position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:60; display:none; align-items:center; justify-content:center; padding:16px;">
        <div style="background:#fff; border-radius:16px; padding:24px; max-width:380px; width:100%; text-align:center;">
            <div style="width:64px; height:64px; background:#dcfce7; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
                <i class="fas fa-check" style="color:#22c55e; font-size:24px;"></i>
            </div>
            <h3 style="font-size:20px; font-weight:700; color:#1f2937;">¡Pedido Enviado!</h3>
            <p style="color:#6b7280; margin-top:4px;" id="success-order-number"></p>
            <p style="font-size:14px; color:#9ca3af; margin-top:8px;">Tu pedido llegará a cocina en un momento.</p>
            <button onclick="closeSuccessModal()"
                    style="margin-top:16px; width:100%; background:#2563eb; color:#fff; padding:12px; border-radius:8px; font-weight:600; border:none; cursor:pointer;">
                Aceptar
            </button>
            <button onclick="enableAddMore()"
                    style="margin-top:8px; width:100%; background:#fff; color:#2563eb; padding:12px; border-radius:8px; font-weight:600; border:2px solid #2563eb; cursor:pointer;">
                <i class="fas fa-plus" style="margin-right:6px;"></i> Agregar más items
            </button>
        </div>
    </div>

<script>
// =========================================
// MENU QR - CART SYSTEM
// =========================================
const TABLE_SLUG = document.body.dataset.table;
const TABLE_NAME = document.body.dataset.tableName;
const STORAGE_KEY = `qr_cart_${TABLE_SLUG}`;

let cart = JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
let drawerOpen = false;
let currentOrderId = localStorage.getItem(`qr_order_id_${TABLE_SLUG}`) || null;
let currentOrderNumber = localStorage.getItem(`qr_order_num_${TABLE_SLUG}`) || null;

// =========================================
// CATEGORY FILTER
// =========================================
function filterCategory(catId) {
    document.querySelectorAll('.category-pill').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.cat == catId);
    });
    document.querySelectorAll('.dish-section').forEach(section => {
        if (catId === 'all') {
            section.classList.remove('hide');
        } else {
            section.classList.toggle('hide', section.dataset.category != catId);
        }
    });
}

// =========================================
// CART MANAGEMENT
// =========================================
function addToCart(id, name, price, btn) {
    const idx = cart.findIndex(i => i.id === id);
    if (idx !== -1) {
        cart[idx].quantity += 1;
        cart[idx].total = cart[idx].quantity * cart[idx].price;
    } else {
        cart.push({ id, name, price: parseFloat(price), quantity: 1, total: parseFloat(price) });
    }
    saveCart();
    updateUI();

    // Button feedback
    if (btn) {
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check mr-1"></i> Listo';
        btn.classList.replace('bg-blue-500', 'bg-green-500');
        setTimeout(() => { btn.innerHTML = orig; btn.classList.replace('bg-green-500', 'bg-blue-500'); }, 800);
    }
}

// Event delegation for add-to-cart buttons (avoids inline onclick issues with special chars)
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.add-to-cart-btn');
    if (!btn) return;
    const id = parseInt(btn.dataset.id);
    const name = btn.dataset.name;
    const price = parseFloat(btn.dataset.price);
    addToCart(id, name, price, btn);
});

function updateQty(id, delta) {
    const idx = cart.findIndex(i => i.id === id);
    if (idx === -1) return;
    cart[idx].quantity += delta;
    if (cart[idx].quantity <= 0) {
        cart.splice(idx, 1);
    } else {
        cart[idx].total = cart[idx].quantity * cart[idx].price;
    }
    saveCart();
    updateUI();
}

function clearCart() {
    if (cart.length === 0) return;
    cart = [];
    localStorage.removeItem(STORAGE_KEY);
    updateUI();
}

function saveCart() {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(cart));
}

// =========================================
// UI UPDATES
// =========================================
function updateUI() {
    const totalItems = cart.reduce((s, i) => s + i.quantity, 0);
    const totalPrice = cart.reduce((s, i) => s + i.total, 0);

    // Bar (always visible)
    document.getElementById('bar-count').textContent = totalItems;
    document.getElementById('bar-label').textContent = totalItems > 0
        ? `Ver mi pedido (${totalItems} ${totalItems === 1 ? 'item' : 'items'})`
        : 'Tu carrito está vacío';
    document.getElementById('bar-total').textContent = totalItems > 0 ? `$${totalPrice.toFixed(2)}` : '';
    if (totalItems === 0 && drawerOpen) toggleCart();
    document.getElementById('drawer-total').textContent = `$${totalPrice.toFixed(2)}`;

    // Send button
    const sendBtn = document.getElementById('send-order');
    sendBtn.disabled = totalItems === 0;
    sendBtn.style.opacity = totalItems === 0 ? '0.4' : '1';
    sendBtn.style.cursor = totalItems === 0 ? 'not-allowed' : 'pointer';

    // Cart items
    const container = document.getElementById('cart-items');
    if (cart.length === 0) {
        container.innerHTML = '<p id="empty-msg" class="text-center text-gray-400 py-8">El carrito está vacío</p>';
        return;
    }

    container.innerHTML = cart.map(item => `
        <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
            <div class="flex-1 min-w-0">
                <p class="font-medium text-sm text-gray-800 truncate">${item.name}</p>
                <p class="text-xs text-gray-500">$${item.price.toFixed(2)} c/u</p>
            </div>
            <div class="flex items-center gap-2 ml-3">
                <button onclick="updateQty(${item.id}, -1)"
                        class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 active:bg-gray-300">
                    <i class="fas fa-minus text-xs"></i>
                </button>
                <span class="font-semibold text-sm w-5 text-center">${item.quantity}</span>
                <button onclick="updateQty(${item.id}, 1)"
                        class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 active:bg-gray-300">
                    <i class="fas fa-plus text-xs"></i>
                </button>
            </div>
            <span class="font-bold text-sm text-gray-800 ml-3 w-16 text-right">$${item.total.toFixed(2)}</span>
        </div>
    `).join('');
}

// =========================================
// DRAWER TOGGLE
// =========================================
function toggleCart() {
    drawerOpen = !drawerOpen;
    document.getElementById('cart-drawer').style.transform = drawerOpen ? 'translateY(0)' : 'translateY(100%)';
    const backdrop = document.getElementById('backdrop');
    backdrop.style.opacity = drawerOpen ? '1' : '0';
    backdrop.style.pointerEvents = drawerOpen ? 'auto' : 'none';
    document.body.style.overflow = drawerOpen ? 'hidden' : '';
}

// =========================================
// SEND ORDER
// =========================================
async function handleSendOrder() {
    if (cart.length === 0) return;

    const customerName = document.getElementById('customer-name').value.trim() || 'Cliente QR';
    const notes = document.getElementById('order-notes').value.trim();
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    const btn = document.getElementById('send-order');
    const origHTML = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Enviando...';
    btn.disabled = true;

    try {
        let url, body;

        if (currentOrderId) {
            // Add items to existing order
            url = `/api/orders/${currentOrderId}/items`;
            body = { items: cart.map(i => ({ id: i.id, price: i.price, quantity: i.quantity })) };
        } else {
            // Create new order
            url = '/api/orders';
            body = {
                table: TABLE_NAME,
                customer: customerName,
                notes: notes || null,
                items: cart.map(i => ({ id: i.id, price: i.price, quantity: i.quantity }))
            };
        }

        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(body)
        });

        const data = await res.json();
        if (!res.ok) throw new Error(data.message || `Error ${res.status}`);

        // Save order reference for adding more items later
        currentOrderId = data.order.id;
        currentOrderNumber = data.order.order_number;
        localStorage.setItem(`qr_order_id_${TABLE_SLUG}`, currentOrderId);
        localStorage.setItem(`qr_order_num_${TABLE_SLUG}`, currentOrderNumber);

        // Clear cart
        cart = [];
        localStorage.removeItem(STORAGE_KEY);
        document.getElementById('customer-name').value = '';
        document.getElementById('order-notes').value = '';
        if (drawerOpen) toggleCart();
        updateUI();

        // Show success modal
        document.getElementById('success-order-number').textContent = currentOrderNumber;
        document.getElementById('success-modal').style.display = 'flex';

    } catch (err) {
        // If error is about closed order, clear reference and retry as new order
        if (err.message.includes('entregada') || err.message.includes('cancelada')) {
            clearOrderReference();
            alert('La orden anterior ya fue cerrada. Se creará una nueva orden.');
            handleSendOrder(); // Retry as new order
            return;
        }
        alert('Error al enviar el pedido: ' + err.message);
    } finally {
        btn.innerHTML = origHTML;
        btn.disabled = cart.length === 0;
    }
}

function closeSuccessModal() {
    document.getElementById('success-modal').style.display = 'none';
}

function enableAddMore() {
    document.getElementById('success-modal').style.display = 'none';
    // Update send button text to indicate adding to existing order
    document.getElementById('send-order').innerHTML =
        '<i class="fas fa-plus-circle" style="margin-right:8px;"></i> Agregar a Orden ' + currentOrderNumber;
}

// =========================================
// ORDER STATUS CHECK
// =========================================
async function checkOrderStatus() {
    if (!currentOrderId) return;

    try {
        const res = await fetch(`/api/orders/${currentOrderId}`, {
            headers: { 'Accept': 'application/json' }
        });

        if (!res.ok) {
            // Order not found, clear reference
            clearOrderReference();
            return;
        }

        const data = await res.json();

        if (data.is_closed) {
            // Order is closed (delivered + paid), start new session
            clearOrderReference();
            showNewSessionNotice(data.order.order_number);
        } else {
            // Order is still active, update button
            document.getElementById('send-order').innerHTML =
                '<i class="fas fa-plus-circle" style="margin-right:8px;"></i> Agregar a Orden ' + currentOrderNumber;
        }
    } catch (err) {
        console.error('Error checking order status:', err);
    }
}

function clearOrderReference() {
    currentOrderId = null;
    currentOrderNumber = null;
    localStorage.removeItem(`qr_order_id_${TABLE_SLUG}`);
    localStorage.removeItem(`qr_order_num_${TABLE_SLUG}`);
    document.getElementById('send-order').innerHTML =
        '<i class="fas fa-paper-plane" style="margin-right:8px;"></i> Enviar Pedido';
}

function showNewSessionNotice(prevOrderNumber) {
    // Show a brief toast notification
    const toast = document.createElement('div');
    toast.style.cssText = 'position:fixed; top:80px; left:50%; transform:translateX(-50%); background:#1f2937; color:#fff; padding:12px 20px; border-radius:8px; z-index:100; font-size:14px; box-shadow:0 4px 12px rgba(0,0,0,0.2); display:flex; align-items:center; gap:8px;';
    toast.innerHTML = `<i class="fas fa-utensils" style="color:#22c55e;"></i> Nueva sesión iniciada. Orden anterior: ${prevOrderNumber}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}

// =========================================
// INIT
// =========================================
document.addEventListener('DOMContentLoaded', () => {
    updateUI();
    // Check if existing order is still active
    checkOrderStatus();
});

// Expose globals for onclick handlers
window.addToCart = addToCart;
window.updateQty = updateQty;
window.clearCart = clearCart;
window.filterCategory = filterCategory;
window.handleSendOrder = handleSendOrder;
window.toggleCart = toggleCart;
window.closeSuccessModal = closeSuccessModal;
window.enableAddMore = enableAddMore;
window.clearOrderReference = clearOrderReference;
window.checkOrderStatus = checkOrderStatus;
</script>
</body>
</html>
