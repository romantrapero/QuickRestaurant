<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'QuickRestaurant OMS - Ordering' }}</title>
    
    <!-- Tailwind CSS -->
    @vite('resources/css/app.css')
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Livewire Styles (solo una vez) -->
    @livewireStyles
    
    <style>
        .category-btn.active {
            background-color: #3b82f6;
            color: white;
        }
        .dish-card {
            transition: transform 0.15s, box-shadow 0.15s;
        }
        [x-cloak] { display: none !important; }

        /* iPad POS optimizations */
        .pos-viewport {
            height: 100dvh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            touch-action: manipulation;
        }
        .pos-content-scroll {
            -webkit-overflow-scrolling: touch;
            overscroll-behavior: none;
        }
    </style>
</head>
<body class="bg-gray-100">
    {{ $slot }}
    @livewireScripts
    
    <!-- Scripts adicionales se pueden agregar con @stack('scripts') -->
    @stack('scripts')
</body>
</html>