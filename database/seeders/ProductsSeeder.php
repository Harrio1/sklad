<?php

namespace Database\Seeders;

use App\Models\Products;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Стол рабочий',
                'price' => 7500.00,
                'markup' => 20.00,
                'total_price' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Стул офисный',
                'price' => 3200.00,
                'markup' => 25.00,
                'total_price' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Шкаф для документов',
                'price' => 12500.00,
                'markup' => 18.00,
                'total_price' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Тумба выдвижная',
                'price' => 5800.00,
                'markup' => 22.00,
                'total_price' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Полка настенная',
                'price' => 2100.00,
                'markup' => 30.00,
                'total_price' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Стеллаж металлический',
                'price' => 8950.00,
                'markup' => 15.00,
                'total_price' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Вешалка напольная',
                'price' => 2300.00,
                'markup' => 35.00,
                'total_price' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Компьютерный стол',
                'price' => 9500.00,
                'markup' => 20.00,
                'total_price' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Кресло руководителя',
                'price' => 15000.00,
                'markup' => 25.00,
                'total_price' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Диван офисный',
                'price' => 25000.00,
                'markup' => 15.00,
                'total_price' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        
        foreach ($products as $product) {
            Products::create($product);
        }
    }
} 