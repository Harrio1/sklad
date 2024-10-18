<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Модель для работы с таблицей номенклатур в базе данных
class Nomenclatures extends Model
{
    use HasFactory;
    
    // Указываем, какие поля могут быть массово заполнены
    protected $fillable = [
        'name', 
        'supplier_id', 
        'price_per_unit', 
        'total_quantity', 
        'total_price',
        'unit_of_measurement'
    ];

    // Определяем связь с моделью Suppliers
    public function supplier()
    {
        return $this->belongsTo(Suppliers::class);
    }

    // Определяем связь с моделью Products через таблицу products__nomenclatures
    public function products()
    {
        return $this->belongsToMany(Products::class, 'products__nomenclatures', 'nomenclature_id', 'product_id')
                ->withPivot('quantity', 'price');
    }

    // Определяем связь с моделью Supplies
    public function supplies()
    {
        return $this->hasMany(Supplies::class, 'nomenclature_id');
    }
}
