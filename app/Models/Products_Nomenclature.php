<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Модель для работы с таблицей связей продуктов и номенклатур
class Products_Nomenclature extends Model
{
    use HasFactory;

    // Указываем таблицу, с которой работает модель
    protected $table = 'products__nomenclatures';

    // Указываем, какие поля могут быть массово заполнены
    protected $fillable = ['product_id', 'nomenclature_id', 'quantity', 'price'];

    // Определяем связь с моделью Products
    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    // Определяем связь с моделью Nomenclatures
    public function nomenclature()
    {
        return $this->belongsTo(Nomenclatures::class, 'nomenclature_id');
    }
}
