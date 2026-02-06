<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_configs', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_comercial');
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->text('direccion')->nullable();
            $table->integer('numero_mesas')->default(10);
            $table->decimal('iva_porcentaje', 5, 2)->default(16.00);
            $table->time('horario_apertura')->nullable();
            $table->time('horario_cierre')->nullable();
            $table->json('dias_operacion')->nullable();
            $table->string('moneda')->default('MXN');
            $table->string('timezone')->default('America/Mexico_City');
            $table->timestamps();
        });

        // Insertar configuración por defecto
        DB::table('restaurant_configs')->insert([
            'nombre_comercial' => 'QuickRestaurant',
            'numero_mesas' => 10,
            'iva_porcentaje' => 16.00,
            'moneda' => 'MXN',
            'timezone' => 'America/Mexico_City',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_configs');
    }
};
