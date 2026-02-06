<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@restaurant.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        $this->call([
            CategorySeeder::class,
            TableSeeder::class,
            DishSeeder::class,
            OrderSeeder::class,
            OrderItemSeeder::class,
        ]);

        // Resetear secuencias DESPUÉS de todos los seeders
        $this->resetSequences();
    }

    private function resetSequences(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        $tables = DB::select("
            SELECT tablename 
            FROM pg_tables 
            WHERE schemaname = 'public'
            AND tablename NOT LIKE '%_pivot'
        ");

        foreach ($tables as $table) {
            $tableName = $table->tablename;

            // Verificar si la tabla tiene una columna 'id'
            $hasIdColumn = DB::selectOne("
                SELECT EXISTS (
                    SELECT 1 
                    FROM information_schema.columns 
                    WHERE table_schema = 'public' 
                    AND table_name = '{$tableName}' 
                    AND column_name = 'id'
                ) as exists
            ");

            if (! $hasIdColumn->exists) {
                continue;
            }

            // Verificar si la tabla tiene una secuencia para el id
            $sequence = DB::selectOne("
                SELECT pg_get_serial_sequence('{$tableName}', 'id') as sequence
            ");

            if ($sequence && $sequence->sequence) {
                // Ajustar la secuencia al máximo id existente + 1
                DB::statement("
                    SELECT setval(
                        '{$sequence->sequence}', 
                        COALESCE((SELECT MAX(id) FROM {$tableName}), 0) + 1, 
                        false
                    )
                ");
            }
        }
    }
}
