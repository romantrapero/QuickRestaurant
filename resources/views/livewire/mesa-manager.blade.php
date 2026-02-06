<div class="min-h-screen bg-gray-900 text-gray-100">
    {{-- Header --}}
    <div class="bg-gray-800 border-b border-gray-700 px-6 py-4">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-cyan-400 flex items-center gap-2">
                    <i class="fas fa-th-large"></i>
                    Gestión de Mesas
                </h1>
                <p class="text-gray-400 text-sm mt-1">Control de acceso al menú QR</p>
            </div>

            {{-- Contadores --}}
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex gap-2">
                    <div class="bg-gray-700/50 px-3 py-2 rounded-lg border border-gray-600 min-w-[80px]">
                        <div class="text-xl font-bold text-gray-300 text-center">{{ $estadisticas['total'] }}</div>
                        <div class="text-xs text-gray-400 text-center">Total</div>
                    </div>
                    <div class="bg-green-900/40 px-3 py-2 rounded-lg border border-green-700/50 min-w-[80px]">
                        <div class="text-xl font-bold text-green-300 text-center">{{ $estadisticas['disponibles'] }}</div>
                        <div class="text-xs text-green-400 text-center">Libres</div>
                    </div>
                    <div class="bg-orange-900/40 px-3 py-2 rounded-lg border border-orange-700/50 min-w-[80px]">
                        <div class="text-xl font-bold text-orange-300 text-center">{{ $estadisticas['ocupadas'] }}</div>
                        <div class="text-xs text-orange-400 text-center">Ocupadas</div>
                    </div>
                    <div class="bg-red-900/40 px-3 py-2 rounded-lg border border-red-700/50 min-w-[80px]">
                        <div class="text-xl font-bold text-red-300 text-center">{{ $estadisticas['bloqueadas'] }}</div>
                        <div class="text-xs text-red-400 text-center">Bloqueadas</div>
                    </div>
                </div>
                <a href="/pos" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition flex items-center gap-2">
                    <i class="fas fa-cash-register"></i> POS
                </a>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="flex flex-wrap gap-2 mt-4">
            <button wire:click="cambiarFiltro('todas')"
                    class="px-4 py-2 rounded-lg font-medium transition {{ $filtroEstado === 'todas' ? 'bg-cyan-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                Todas
            </button>
            <button wire:click="cambiarFiltro('disponible')"
                    class="px-4 py-2 rounded-lg font-medium transition {{ $filtroEstado === 'disponible' ? 'bg-green-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                Disponibles
            </button>
            <button wire:click="cambiarFiltro('ocupada')"
                    class="px-4 py-2 rounded-lg font-medium transition {{ $filtroEstado === 'ocupada' ? 'bg-orange-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                Ocupadas
            </button>
            <button wire:click="cambiarFiltro('bloqueada')"
                    class="px-4 py-2 rounded-lg font-medium transition {{ $filtroEstado === 'bloqueada' ? 'bg-red-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                Bloqueadas
            </button>
        </div>
    </div>

    {{-- Mensaje de notificación --}}
    @if($mensaje)
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => { show = false; $wire.ocultarMensaje(); }, 3000)"
             class="fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg {{ $tipoMensaje === 'success' ? 'bg-green-600' : ($tipoMensaje === 'warning' ? 'bg-yellow-600' : 'bg-red-600') }} text-white font-medium"
             x-transition>
            <i class="fas {{ $tipoMensaje === 'success' ? 'fa-check-circle' : ($tipoMensaje === 'warning' ? 'fa-exclamation-triangle' : 'fa-times-circle') }} mr-2"></i>
            {{ $mensaje }}
        </div>
    @endif

    {{-- Grid de Mesas --}}
    <div class="p-4 md:p-6">
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 0.75rem;">
            @foreach($mesas as $mesa)
                @php
                    $borderColor = match($mesa['estado']) {
                        'disponible' => 'border-green-500',
                        'ocupada' => 'border-orange-500',
                        'bloqueada' => 'border-red-500',
                        default => 'border-gray-600'
                    };
                    $bgColor = match($mesa['estado']) {
                        'disponible' => 'bg-green-900/20',
                        'ocupada' => 'bg-orange-900/20',
                        'bloqueada' => 'bg-red-900/20',
                        default => 'bg-gray-800'
                    };
                    $statusBg = match($mesa['estado']) {
                        'disponible' => 'bg-green-900 text-green-300',
                        'ocupada' => 'bg-orange-900 text-orange-300',
                        'bloqueada' => 'bg-red-900 text-red-300',
                        default => 'bg-gray-700 text-gray-300'
                    };
                @endphp

                <div class="bg-gray-800 rounded-xl border-l-4 {{ $borderColor }} {{ $bgColor }} p-3 shadow-lg">
                    {{-- Header: Número + Estado --}}
                    <div class="flex justify-between items-center mb-2">
                        <div class="text-3xl font-bold text-white">{{ $mesa['numero'] }}</div>
                        <span class="px-2 py-1 text-xs rounded-full {{ $statusBg }}">
                            {{ $mesa['estado_label'] }}
                        </span>
                    </div>

                    {{-- QR Bloqueado indicator --}}
                    @if(!$mesa['qr_access'])
                        <div class="text-xs text-red-400 mb-2">
                            <i class="fas fa-lock"></i> QR Bloqueado
                        </div>
                    @endif

                    {{-- Total de orden (si existe) --}}
                    @if($mesa['orden'])
                        <div class="text-sm text-cyan-400 font-bold mb-2">
                            ${{ number_format($mesa['orden']['total'], 2) }}
                        </div>
                    @endif

                    {{-- Botón único de acción --}}
                    <button wire:click="toggleQrAccess({{ $mesa['id'] }})"
                            class="w-full min-h-[44px] {{ $mesa['qr_access'] ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white py-2 rounded-lg font-medium flex items-center justify-center gap-2 transition">
                        <i class="fas {{ $mesa['qr_access'] ? 'fa-lock' : 'fa-unlock' }}"></i>
                        {{ $mesa['qr_access'] ? 'Bloquear QR' : 'Desbloquear QR' }}
                    </button>
                </div>
            @endforeach
        </div>

        @if(empty($mesas))
            <div class="text-center py-16">
                <i class="fas fa-inbox text-gray-600 text-6xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-400 mb-2">No hay mesas</h3>
                <p class="text-gray-500">Configura el número de mesas en Administración.</p>
            </div>
        @endif
    </div>
</div>
