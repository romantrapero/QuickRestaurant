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
                'name' => 'Aguachiles',
                'order' => 2,
                'is_active' => true,
                'created_at' => '2025-12-13 07:54:18',
                'updated_at' => '2025-12-14 18:05:15',
            ],
            [
                'id' => 2,
                'name' => 'Ceviches',
                'order' => 1,
                'is_active' => true,
                'created_at' => '2025-12-13 07:54:18',
                'updated_at' => '2025-12-14 00:04:08',
            ],
            [
                'id' => 3,
                'name' => 'Bebidas',
                'order' => 3,
                'is_active' => true,
                'created_at' => '2025-12-13 07:54:18',
                'updated_at' => '2025-12-14 00:15:32',
            ],
            [
                'id' => 4,
                'name' => 'Extras',
                'order' => 4,
                'is_active' => true,
                'created_at' => '2025-12-14 00:27:15',
                'updated_at' => '2025-12-14 02:15:02',
            ],
        ]);
    }
}
