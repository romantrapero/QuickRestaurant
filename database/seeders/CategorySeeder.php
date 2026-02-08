<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::insert([
            [
                'id' => 1,
                'name' => 'Aguachile',
                'order' => 1,
                'is_active' => true,
                'print_station' => 'cold_bar',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Ceviche',
                'order' => 2,
                'is_active' => true,
                'print_station' => 'cold_bar',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Taquería del Pacífico',
                'order' => 3,
                'is_active' => true,
                'print_station' => 'hot_bar',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Especiales',
                'order' => 4,
                'is_active' => true,
                'print_station' => 'hot_bar',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Postres',
                'order' => 5,
                'is_active' => true,
                'print_station' => 'cashier',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'name' => 'Bebidas',
                'order' => 6,
                'is_active' => true,
                'print_station' => 'cashier',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'name' => 'Niños',
                'order' => 7,
                'is_active' => true,
                'print_station' => 'hot_bar',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'name' => 'Extras',
                'order' => 8,
                'is_active' => true,
                'print_station' => 'none',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
