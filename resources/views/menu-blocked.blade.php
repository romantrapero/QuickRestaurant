<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Menú - {{ $config->nombre_comercial }}</title>

    @vite('resources/css/app.css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * { -webkit-tap-highlight-color: transparent; }
        body { padding-bottom: 2rem; }
        .category-pill.active { background-color: #2563eb; color: white; }
        .dish-section { scroll-margin-top: 4.5rem; }
        .hide { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

    {{-- ===== HEADER ===== --}}
    <header class="bg-white shadow-sm sticky top-0 z-30">
        <div class="px-4 py-3 flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-utensils text-blue-500 mr-1"></i> {{ $config->nombre_comercial }}
                </h1>
            </div>
            <div class="text-right">
                <span class="inline-flex items-center bg-blue-100 text-blue-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                    <i class="fas fa-map-pin mr-1"></i> {{ $tableName }}
                </span>
            </div>
        </div>

        {{-- Alert de Solo Lectura --}}
        <div class="bg-orange-50 border-t border-b border-orange-200 px-4 py-3">
            <div class="flex items-center gap-2 text-orange-800">
                <i class="fas fa-eye text-orange-500 text-lg"></i>
                <div class="text-sm">
                    <strong>Modo Solo Vista</strong>
                    <p class="text-xs text-orange-600">Consulta nuestro menú. Para ordenar, solicita asistencia al personal.</p>
                </div>
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
                        <div class="bg-white rounded-xl shadow-sm overflow-hidden flex flex-col opacity-90">
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
                                    <button class="bg-gray-300 text-gray-500 px-3 py-1.5 rounded-lg text-sm font-semibold cursor-not-allowed" disabled>
                                        <i class="fas fa-lock mr-1"></i> Bloqueado
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach
    </main>

    {{-- ===== FOOTER INFO ===== --}}
    <footer class="bg-white border-t border-gray-200 mt-8 py-4 px-4 text-center">
        <div class="text-gray-600 text-sm mb-2">
            <i class="fas fa-info-circle text-blue-500 mr-1"></i>
            Para realizar un pedido, solicita asistencia al personal.
        </div>
        @if($config->telefono)
            <div class="text-xs text-gray-500">
                <i class="fas fa-phone mr-1"></i> {{ $config->telefono }}
            </div>
        @endif
    </footer>

<script>
// Category filter
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
</script>
</body>
</html>
