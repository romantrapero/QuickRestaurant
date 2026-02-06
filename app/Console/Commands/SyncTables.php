<?php

namespace App\Console\Commands;

use App\Models\RestaurantConfig;
use App\Models\Table;
use Illuminate\Console\Command;

class SyncTables extends Command
{
    protected $signature = 'tables:sync';

    protected $description = 'Sincroniza el número de mesas con la configuración';

    public function handle()
    {
        $config = RestaurantConfig::config();
        $targetMesas = $config->numero_mesas;
        $currentMesas = Table::count();

        $this->info("Mesas actuales: {$currentMesas}");
        $this->info("Mesas objetivo: {$targetMesas}");

        if ($currentMesas === $targetMesas) {
            $this->info('✅ No hay cambios necesarios.');

            return 0;
        }

        if ($currentMesas < $targetMesas) {
            // Crear nuevas mesas
            $toCreate = $targetMesas - $currentMesas;
            $lastNumber = Table::max('orden_visual') ?? 0;

            for ($i = 1; $i <= $toCreate; $i++) {
                $newNumber = $currentMesas + $i;
                Table::create([
                    'numero' => (string) $newNumber,
                    'nombre' => "Mesa {$newNumber}",
                    'capacidad' => 4,
                    'estado' => Table::ESTADO_DISPONIBLE,
                    'qr_access' => true,
                    'is_active' => true,
                    'orden_visual' => $lastNumber + $i,
                ]);
            }

            $this->info("✅ {$toCreate} mesas creadas.");
        } else {
            // Desactivar mesas sobrantes (no eliminar)
            $toDisable = $currentMesas - $targetMesas;

            Table::orderBy('orden_visual', 'desc')
                ->limit($toDisable)
                ->update(['is_active' => false]);

            $this->warn("⚠️  {$toDisable} mesas desactivadas (no eliminadas).");
        }

        return 0;
    }
}
