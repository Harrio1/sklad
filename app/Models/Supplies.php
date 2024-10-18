<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Модель для работы с таблицей поставок
class Supplies extends Model
{
    use HasFactory;

    // Указываем, какие поля могут быть массово заполнены
    protected $fillable = [
        'nomenclature_id',
        'supply_date',
        'quantity',
        'unit',
        'price'
    ];

    // Определяем связь с моделью Nomenclatures
    public function nomenclature()
    {
        return $this->belongsTo(Nomenclatures::class);
    }
}
