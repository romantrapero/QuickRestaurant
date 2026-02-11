<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_modifications', function (Blueprint $table) {
            // Guardar valores anteriores/nuevos para comparación
            $table->text('previous_value')->nullable()->after('reason');
            $table->text('new_value')->nullable()->after('previous_value');
        });

        // Nota: Los nuevos tipos 'notes_changed' y 'modifiers_changed'
        // se pueden usar con el enum existente si es necesario,
        // aunque no estén definidos formalmente en el CHECK constraint.
        // Laravel los validará a nivel de aplicación.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_modifications', function (Blueprint $table) {
            $table->dropColumn(['previous_value', 'new_value']);
        });

        // Nota: PostgreSQL no permite eliminar valores de un enum fácilmente.
        // Los valores 'notes_changed' y 'modifiers_changed' permanecerán en el tipo.
        // Para eliminarlo completamente, sería necesario recrear el tipo.
    }
};
