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
        Schema::create('dish_modifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dish_id')->constrained()->onDelete('cascade');
            $table->string('name'); // "Extra queso", "Sin cebolla", "Término medio"
            $table->enum('type', ['extra', 'exception']); // extra=con precio, exception=sin precio
            $table->decimal('price', 10, 2)->default(0); // Precio adicional (0 para excepciones)
            $table->boolean('is_available')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();

            // Indexes
            $table->index(['dish_id', 'is_available']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dish_modifiers');
    }
};
