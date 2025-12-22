<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    // Permitir asignación masiva en estos campos
    protected $fillable = [
        'name',
        'order',
        'is_active',
    ];

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
