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
        Schema::create('order_item_modifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('dish_modifier_id')->constrained()->onDelete('restrict');
            $table->integer('quantity')->default(1); // Para "Extra queso x2"
            $table->decimal('unit_price', 10, 2); // Snapshot del precio al momento de ordenar
            $table->decimal('total_price', 10, 2); // quantity * unit_price
            $table->timestamps();

            // Indexes
            $table->index('order_item_id');
            $table->unique(['order_item_id', 'dish_modifier_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item_modifiers');
    }
};
