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
        Schema::table('order_items', function (Blueprint $table) {
            $table->timestamp('printed_at')->nullable()->after('total_price');
            $table->enum('status', ['pending', 'sent_to_kitchen', 'preparing', 'ready', 'delivered', 'cancelled'])
                ->default('pending')
                ->after('printed_at');
            $table->integer('print_count')->default(0)->after('status');

            // Indexes para queries de items pendientes de impresión
            $table->index(['order_id', 'printed_at']);
            $table->index(['order_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['order_id', 'printed_at']);
            $table->dropIndex(['order_id', 'status']);
            $table->dropColumn(['printed_at', 'status', 'print_count']);
        });
    }
};
