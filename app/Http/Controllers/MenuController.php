<?php

namespace App\Http\Controllers;

use App\Models\Category;

class MenuController extends Controller
{
    public function show(string $table = 'general')
    {
        $categories = Category::where('is_active', true)
            ->orderBy('order')
            ->with(['dishes' => fn($q) => $q->where('is_available', true)])
            ->get()
            ->filter(fn($cat) => $cat->dishes->isNotEmpty());

        $tableName = 'QR - ' . ucwords(str_replace('-', ' ', $table));

        return view('menu', [
            'categories' => $categories,
            'table' => $table,
            'tableName' => $tableName,
        ]);
    }
}
