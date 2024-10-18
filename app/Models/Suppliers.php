<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Модель для работы с таблицей поставщиков в базе данных
class Suppliers extends Model
{
    use HasFactory;

    // Указываем, какие поля могут быть массово заполнены
    protected $fillable = [
        'name',
        'address',
        'comments',
        'phone',
    ];
    
}
