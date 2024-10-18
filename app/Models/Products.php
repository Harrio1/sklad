<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Модель для работы с таблицей продуктов в базе данных
class Products extends Model
{
    use HasFactory;

    // Указываем, какие поля могут быть массово заполнены
    protected $fillable = ['name', 'markup', 'price', 'total_price'];

    // Определяем связь с моделью Nomenclatures
    public function nomenclatures()
    {
        return $this->belongsToMany(Nomenclatures::class, 'products__nomenclatures', 'product_id', 'nomenclature_id')
                    ->withPivot('quantity', 'price');
    }

    // Метод для расчета себестоимости
    public function calculateCostPrice()
    {
        return $this->nomenclatures->sum(function ($nomenclature) {
            return $nomenclature->pivot->price * $nomenclature->pivot->quantity;
        });
    }

    // Метод для расчета итоговой цены с учетом наценки
    public function calculateTotalPrice()
    {
        $costPrice = $this->calculateCostPrice();
        return $costPrice * (1 + $this->markup / 100);
    }

    // Метод для расчета общей цены номенклатуры
    public function calculateTotalNomenclaturePrice()
    {
        return $this->nomenclatures->sum(function ($nomenclature) {
            return $nomenclature->pivot->price * $nomenclature->pivot->quantity;
        });
    }

    // Метод для установки себестоимости
    public function setCostPrice()
    {
        $totalCost = $this->nomenclatures->sum(function ($nomenclature) {
            return $nomenclature->pivot->price * $nomenclature->pivot->quantity;
        });
        $this->price = $totalCost;
        return $this;
    }

    // Метод для установки итоговой цены
    public function setTotalPrice()
    {
        $this->total_price = $this->calculateTotalPrice();
        return $this;
    }
}
