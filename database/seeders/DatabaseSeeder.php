<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        \App\Models\Category::factory()->createMany([
            ['name' => 'Aguachiles', 'order' => 1],
            ['name' => 'Ceviches', 'order' => 2],
            ['name' => 'Bebidas', 'order' => 3],
        ]);

        \App\Models\Dish::factory()->createMany([
            [
                'category_id' => 1,
                'name' => 'Aguachile Tradicional',
                'cost_price' => 130.00,
                'sale_price' => 260.00,
                'preparation_time' => 15
            ],
            [
                'category_id' => 2,
                'name' => 'Ceviche Culichi',
                'cost_price' => 95.00,
                'sale_price' => 230.00,
                'preparation_time' => 15
            ],
            [
                'category_id' => 3,
                'name' => 'Preparado',
                'cost_price' => 25.00,
                'sale_price' => 60.00,
                'preparation_time' => 5
            ],
            
        ]);
    }
}
