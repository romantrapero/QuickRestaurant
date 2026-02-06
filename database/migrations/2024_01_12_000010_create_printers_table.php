<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('printers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('station', ['cold_bar', 'hot_bar', 'cashier']);
            $table->enum('connection_type', ['usb', 'network']);
            $table->string('usb_path')->nullable();
            $table->string('network_ip')->nullable();
            $table->integer('network_port')->default(9100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('station');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('printers');
    }
};
