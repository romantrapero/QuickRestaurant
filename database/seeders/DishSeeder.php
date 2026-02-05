<?php

namespace Database\Seeders;

use App\Models\Dish;
use Illuminate\Database\Seeder;

class DishSeeder extends Seeder
{
    public function run(): void
    {
        Dish::insert([
            [
                'id' => 1,
                'category_id' => 1,
                'name' => "Aguachile Celestino's",
                'description' => 'Camarón enchilado, camarón cocido y pulpo en salsa verde, acompañado con pepino y cebolla cortado en julianas. Todos nuestros platillos van acompañados de aguacate, tostadas y nuestras salsas.',
                'cost_price' => 125.00,
                'sale_price' => 285.00,
                'is_available' => true,
                'image_url' => '01KCC5ZNW6P7BGFJRJ5XQX8VSG.png',
                'preparation_time' => 15,
                'created_at' => '2025-12-13 07:54:18',
                'updated_at' => '2025-12-14 05:36:18',
            ],
            [
                'id' => 2,
                'category_id' => 2,
                'name' => 'Ceviche Culichi',
                'description' => 'Camarón cocido, callo de róbalo y pulpo sumergido en kermato, acompañando con pepino, tomate y cebolla cortados finamente. Todos nuestros platillos van acompañados de aguacate, tostadas y nuestras salsas.',
                'cost_price' => 95.00,
                'sale_price' => 230.00,
                'is_available' => true,
                'image_url' => '01KCC6A4FDF4JCZAZZX46P1ZXP.png',
                'preparation_time' => 15,
                'created_at' => '2025-12-13 07:54:18',
                'updated_at' => '2025-12-14 00:29:21',
            ],
            [
                'id' => 3,
                'category_id' => 3,
                'name' => 'Preparado Grande',
                'description' => 'Preparato refrescante y especiado a base de Kermato sin alcohol, servido bien frío con hielo. Se realza con un toque de limón fresco y sal, se completa con salsas suaves que resaltan su carácter intenso y equilibrado.',
                'cost_price' => 25.00,
                'sale_price' => 60.00,
                'is_available' => true,
                'image_url' => null,
                'preparation_time' => 5,
                'created_at' => '2025-12-13 07:54:18',
                'updated_at' => '2025-12-14 06:35:33',
            ],
            [
                'id' => 4,
                'category_id' => 4,
                'name' => 'Tostadas',
                'description' => 'Tostadas 100% Sinaloenses',
                'cost_price' => 5.00,
                'sale_price' => 15.00,
                'is_available' => true,
                'image_url' => null,
                'preparation_time' => 0,
                'created_at' => '2025-12-14 01:03:45',
                'updated_at' => '2025-12-14 02:14:37',
            ],
            [
                'id' => 5,
                'category_id' => 1,
                'name' => 'Aguachile del Pacifico',
                'description' => 'Callo de róbalo, atún y pulpo en salsa negra, acompañado con pepino y cebolla cortado en julianas. Todos nuestros platillos van acompañados de aguacate, tostadas y nuestras salsas.',
                'cost_price' => 120.00,
                'sale_price' => 270.00,
                'is_available' => true,
                'image_url' => '01KCDAFF1PY3D4S14D4YQ8J86X.png',
                'preparation_time' => 15,
                'created_at' => '2025-12-14 02:19:18',
                'updated_at' => '2025-12-14 02:19:18',
            ],
            [
                'id' => 6,
                'category_id' => 3,
                'name' => 'Preparado Chico',
                'description' => 'Preparato refrescante y especiado a base de Kermato sin alcohol, servido bien frío con hielo. Se realza con un toque de limón fresco y sal, se completa con salsas suaves que resaltan su carácter intenso y equilibrado.',
                'cost_price' => 15.00,
                'sale_price' => 35.00,
                'is_available' => true,
                'image_url' => null,
                'preparation_time' => 5,
                'created_at' => '2025-12-14 06:34:56',
                'updated_at' => '2025-12-14 06:34:56',
            ],
            [
                'id' => 7,
                'category_id' => 4,
                'name' => 'Tostitos',
                'description' => null,
                'cost_price' => 10.00,
                'sale_price' => 15.00,
                'is_available' => true,
                'image_url' => null,
                'preparation_time' => 0,
                'created_at' => '2025-12-14 06:36:01',
                'updated_at' => '2025-12-14 06:36:01',
            ],
            [
                'id' => 8,
                'category_id' => 2,
                'name' => 'Ceviche Sinaloense',
                'description' => 'Camarón curtido, camarón cocido y pulpo sumergido en kermato, acompañando con pepino tomate y cebolla cortados finamente. Todos nuestros platillos van acompañados de aguacate, tostadas y nuestras salsas.',
                'cost_price' => 95.00,
                'sale_price' => 230.00,
                'is_available' => true,
                'image_url' => '01KCDS8Z6PZX4PZMK3EBJ6B7DE.png',
                'preparation_time' => 15,
                'created_at' => '2025-12-14 06:37:54',
                'updated_at' => '2025-12-14 06:37:54',
            ],
        ]);
    }
}
