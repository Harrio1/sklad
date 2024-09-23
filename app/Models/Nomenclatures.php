<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nomenclatures extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'supplier_id'];

    public function supplier()
    {
        return $this->belongsTo(Suppliers::class);
    }
}
