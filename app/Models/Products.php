<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price'];

    public function nomenclatures()
    {
        return $this->belongsToMany(Nomenclatures::class, 'products__nomenclatures', 'product_id', 'nomenclature_id')
            ->withPivot('quantity', 'price')
            ->withTimestamps();
    }
}
