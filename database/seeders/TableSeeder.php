<?php

namespace Database\Seeders;

use App\Models\RestaurantConfig;
use App\Models\Table;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    public function run(): void
    {
        $config = RestaurantConfig::config();
        $numeroMesas = $config->numero_mesas;

        // Limpiar mesas existentes
        Table::truncate();

        // Crear mesas numeradas
        for ($i = 1; $i <= $numeroMesas; $i++) {
            Table::create([
                'numero' => (string) $i,
                'nombre' => "Mesa {$i}",
                'capacidad' => rand(2, 6),
                'estado' => Table::ESTADO_DISPONIBLE,
                'qr_access' => false,
                'is_active' => true,
                'orden_visual' => $i,
            ]);
        }

        $this->command->info("✅ {$numeroMesas} mesas creadas exitosamente.");
    }
}
