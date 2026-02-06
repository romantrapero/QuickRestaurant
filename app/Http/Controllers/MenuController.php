<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\RestaurantConfig;
use App\Models\Table;

class MenuController extends Controller
{
    public function show(string $tableSlug = 'general')
    {
        // Buscar mesa por número
        $table = Table::where('numero', $tableSlug)
            ->orWhere('numero', str_replace('mesa-', '', $tableSlug))
            ->first();

        // Si no existe la mesa en la BD, retornar 404
        if (! $table) {
            abort(404, 'Mesa no encontrada');
        }

        // Si la mesa no está activa, retornar 404
        if (! $table->is_active) {
            abort(404, 'Mesa no disponible');
        }

        $isBlocked = ! $table->qr_access;
        $tableName = $table->nombre ?? "Mesa {$table->numero}";
        $tableId = $table->id;

        $categories = Category::where('is_active', true)
            ->orderBy('order')
            ->with(['dishes' => fn ($q) => $q->where('is_available', true)])
            ->get()
            ->filter(fn ($cat) => $cat->dishes->isNotEmpty());

        $config = RestaurantConfig::config();

        // Si bloqueada, vista de solo lectura
        if ($isBlocked) {
            return view('menu-blocked', [
                'categories' => $categories,
                'table' => $tableSlug,
                'tableName' => $tableName,
                'tableId' => $tableId,
                'config' => $config,
            ]);
        }

        return view('menu', [
            'categories' => $categories,
            'table' => $tableSlug,
            'tableName' => $tableName,
            'tableId' => $tableId,
            'config' => $config,
        ]);
    }
}
