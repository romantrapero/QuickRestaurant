<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dish extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 
        'name', 
        'description', 
        'cost_price', 
        'sale_price', 
        'is_available', 
        'image_url', 
        'preparation_time'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Accesores para compatibilidad con la vista
    public function getPriceAttribute()
    {
        return $this->sale_price;
    }

    public function getCostAttribute()
    {
        return $this->cost_price;
    }
}
