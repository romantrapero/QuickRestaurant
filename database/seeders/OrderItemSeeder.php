<?php

namespace Database\Seeders;

use App\Models\OrderItem;
use Illuminate\Database\Seeder;

class OrderItemSeeder extends Seeder
{
    public function run(): void
    {
        OrderItem::insert([
            [
                'id' => 28,
                'order_id' => 17,
                'dish_id' => 8,
                'quantity' => 1,
                'unit_price' => 230.00,
                'total_price' => 230.00,
                'special_instructions' => null,
                'created_at' => '2025-12-14 20:10:12',
                'updated_at' => '2025-12-14 20:10:12',
            ],
            [
                'id' => 29,
                'order_id' => 17,
                'dish_id' => 1,
                'quantity' => 1,
                'unit_price' => 285.00,
                'total_price' => 285.00,
                'special_instructions' => null,
                'created_at' => '2025-12-14 20:10:12',
                'updated_at' => '2025-12-14 20:10:12',
            ],
            [
                'id' => 30,
                'order_id' => 18,
                'dish_id' => 5,
                'quantity' => 2,
                'unit_price' => 270.00,
                'total_price' => 540.00,
                'special_instructions' => null,
                'created_at' => '2025-12-14 20:10:45',
                'updated_at' => '2025-12-14 20:10:45',
            ],
            [
                'id' => 31,
                'order_id' => 18,
                'dish_id' => 3,
                'quantity' => 2,
                'unit_price' => 60.00,
                'total_price' => 120.00,
                'special_instructions' => null,
                'created_at' => '2025-12-14 20:10:45',
                'updated_at' => '2025-12-14 20:10:45',
            ],
            [
                'id' => 32,
                'order_id' => 19,
                'dish_id' => 2,
                'quantity' => 1,
                'unit_price' => 230.00,
                'total_price' => 230.00,
                'special_instructions' => null,
                'created_at' => '2025-12-14 20:11:00',
                'updated_at' => '2025-12-14 20:11:00',
            ],
            [
                'id' => 33,
                'order_id' => 19,
                'dish_id' => 8,
                'quantity' => 1,
                'unit_price' => 230.00,
                'total_price' => 230.00,
                'special_instructions' => null,
                'created_at' => '2025-12-14 20:11:00',
                'updated_at' => '2025-12-14 20:11:00',
            ],
        ]);
    }
}
