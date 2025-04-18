<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitOfMeasurement extends Model
{
    use HasFactory;

    /**
     * Имя таблицы, ассоциированной с моделью.
     *
     * @var string
     */
    protected $table = 'units_of_measurement';

    /**
     * Атрибуты, которые можно массово присваивать.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'type',
        'step',
        'min_value',
    ];

    /**
     * Получить номенклатуры, использующие эту единицу измерения.
     */
    public function nomenclatures()
    {
        return $this->hasMany(Nomenclatures::class, 'unit_of_measurement', 'name');
    }
} 