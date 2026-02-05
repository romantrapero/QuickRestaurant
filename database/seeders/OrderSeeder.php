<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        Order::insert([
            [
                'id' => 17,
                'order_number' => 'ORD-20251214-0001',
                'status' => 'delivered',
                'total' => 515.00,
                'table_number' => 'To Go',
                'customer_notes' => 'Poco picante',
                'completed_at' => null,
                'created_at' => '2025-12-14 20:10:12',
                'updated_at' => '2025-12-14 20:11:25',
                'customer_name' => 'Maria',
            ],
            [
                'id' => 18,
                'order_number' => 'ORD-20251214-0002',
                'status' => 'delivered',
                'total' => 660.00,
                'table_number' => 'Uber Eats',
                'customer_notes' => 'Empacar bien',
                'completed_at' => null,
                'created_at' => '2025-12-14 20:10:45',
                'updated_at' => '2025-12-14 20:11:44',
                'customer_name' => 'Sara',
            ],
            [
                'id' => 19,
                'order_number' => 'ORD-20251214-0003',
                'status' => 'delivered',
                'total' => 460.00,
                'table_number' => 'Rappi',
                'customer_notes' => null,
                'completed_at' => null,
                'created_at' => '2025-12-14 20:11:00',
                'updated_at' => '2025-12-14 20:11:56',
                'customer_name' => 'Cliente no identificado',
            ],
        ]);
    }
}
