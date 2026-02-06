<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    public const STATION_COLD_BAR = 'cold_bar';

    public const STATION_HOT_BAR = 'hot_bar';

    public const STATION_CASHIER = 'cashier';

    public const STATION_NONE = 'none';

    // Permitir asignación masiva en estos campos
    protected $fillable = [
        'name',
        'order',
        'is_active',
        'print_station',
    ];

    public static function getPrintStationOptions(): array
    {
        return [
            self::STATION_COLD_BAR => 'Barra Fría',
            self::STATION_HOT_BAR => 'Barra Caliente',
            self::STATION_CASHIER => 'Caja',
            self::STATION_NONE => 'No imprimir',
        ];
    }

    public function getPrintStationLabelAttribute(): string
    {
        return self::getPrintStationOptions()[$this->print_station] ?? $this->print_station;
    }

    public function dishes()
    {
        return $this->hasMany(Dish::class);
    }

    // Obtener todas las categorías
    public static function getAllCategories()
    {
        return self::all();
    }

    // Crear una nueva categoría
    public static function createCategory($data)
    {
        return self::create($data);
    }

    // Obtener una categoría por ID
    public static function getCategoryById($id)
    {
        return self::find($id);
    }

    // Actualizar una categoría
    public static function updateCategory($id, $data)
    {
        $category = self::find($id);
        if ($category) {
            $category->update($data);
        }

        return $category;
    }

    // Eliminar una categoría
    public static function deleteCategory($id)
    {
        $category = self::find($id);
        if ($category) {
            $category->delete();

            return true;
        }

        return false;
    }
}
