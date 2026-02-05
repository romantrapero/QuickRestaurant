<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@restaurant.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        $this->call([
            CategorySeeder::class,
            DishSeeder::class,
            OrderSeeder::class,
            OrderItemSeeder::class,
        ]);
    }
}
