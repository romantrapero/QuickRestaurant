<?php

namespace App\Livewire;

use App\Models\RestaurantConfig;
use App\Models\Table;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Gestión de Mesas')]
class MesaManager extends Component
{
    public $mesas = [];

    public $filtroEstado = 'todas';

    public $config;

    public $mensaje = null;

    public $tipoMensaje = 'success';

    public function __construct()
    {
        if (! Auth::check()) {
            abort(403, 'No autorizado');
        }
    }

    public function mount()
    {
        $this->config = RestaurantConfig::config();
        $this->cargarMesas();
    }

    #[On('mesa-actualizada')]
    public function cargarMesas()
    {
        $query = Table::active()
            ->ordenadas()
            ->with(['orden.items']);

        if ($this->filtroEstado !== 'todas') {
            $query->where('estado', $this->filtroEstado);
        }

        $this->mesas = $query->get()->map(function ($mesa) {
            return [
                'id' => $mesa->id,
                'numero' => $mesa->numero,
                'nombre' => $mesa->nombre ?? "Mesa {$mesa->numero}",
                'capacidad' => $mesa->capacidad,
                'estado' => $mesa->estado,
                'estado_label' => Table::getEstadoOptions()[$mesa->estado],
                'qr_access' => $mesa->qr_access,
                'qr_url' => $mesa->qr_url,
                'tiempo_ocupacion' => $mesa->tiempo_ocupacion,
                'orden' => $mesa->orden ? [
                    'id' => $mesa->orden->id,
                    'order_number' => $mesa->orden->order_number,
                    'total' => $mesa->orden->total,
                    'customer_name' => $mesa->orden->customer_name,
                    'items_count' => $mesa->orden->items->count(),
                    'payment_status' => $mesa->orden->payment_status,
                ] : null,
            ];
        })->toArray();
    }

    public function cambiarFiltro($estado)
    {
        $this->filtroEstado = $estado;
        $this->cargarMesas();
    }

    public function toggleQrAccess($mesaId)
    {
        $mesa = Table::find($mesaId);

        if (! $mesa) {
            return;
        }

        if ($mesa->qr_access) {
            $mesa->bloquear();
            $this->mostrarMensaje("Mesa {$mesa->numero} BLOQUEADA. Solo pueden ver el menú.", 'warning');
        } else {
            $mesa->desbloquear();
            $this->mostrarMensaje("Mesa {$mesa->numero} DESBLOQUEADA. Pueden ordenar.", 'success');
        }

        $this->cargarMesas();
    }

    public function liberarMesa($mesaId)
    {
        $mesa = Table::find($mesaId);

        if (! $mesa) {
            return;
        }

        if ($mesa->orden && $mesa->orden->payment_status !== 'paid') {
            $this->mostrarMensaje('No se puede liberar: orden pendiente de pago.', 'error');

            return;
        }

        $mesa->liberar();
        $this->mostrarMensaje("Mesa {$mesa->numero} liberada.", 'success');
        $this->cargarMesas();
    }

    private function mostrarMensaje($texto, $tipo = 'success')
    {
        $this->mensaje = $texto;
        $this->tipoMensaje = $tipo;
        $this->dispatch('mensaje-mostrado');
    }

    public function ocultarMensaje()
    {
        $this->mensaje = null;
    }

    public function render()
    {
        return view('livewire.mesa-manager', [
            'estadisticas' => $this->getEstadisticas(),
        ]);
    }

    private function getEstadisticas(): array
    {
        $total = Table::active()->count();
        $disponibles = Table::active()->disponibles()->count();
        $ocupadas = Table::active()->where('estado', Table::ESTADO_OCUPADA)->count();
        $bloqueadas = Table::active()->where('estado', Table::ESTADO_BLOQUEADA)->count();

        return compact('total', 'disponibles', 'ocupadas', 'bloqueadas');
    }
}
