<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->string('nombre')->nullable();
            $table->integer('capacidad')->default(4);
            $table->enum('estado', ['disponible', 'ocupada', 'bloqueada'])->default('disponible');
            $table->boolean('qr_access')->default(false);
            $table->foreignId('orden_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->timestamp('ocupada_desde')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('orden_visual')->default(0);
            $table->timestamps();

            $table->index('estado');
            $table->index(['is_active', 'orden_visual']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
