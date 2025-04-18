<?php

namespace Database\Seeders;

use App\Models\Nomenclatures;
use App\Models\Products;
use App\Models\Products_Nomenclature;
use Illuminate\Database\Seeder;

class ProductsNomenclatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Получаем все продукты и номенклатуры
        $products = Products::all();
        $nomenclatures = Nomenclatures::all();
        
        if ($products->isEmpty() || $nomenclatures->isEmpty()) {
            $this->command->info('Добавьте продукты и номенклатуры перед запуском этого сидера!');
            return;
        }
        
        // Для каждого продукта добавляем от 3 до 8 номенклатур
        foreach ($products as $product) {
            // Сбрасываем total_price продукта
            $totalPrice = 0;
            
            // Выбираем случайные номенклатуры для этого продукта
            $randomNomenclatures = $nomenclatures->random(rand(3, min(8, $nomenclatures->count())));
            
            foreach ($randomNomenclatures as $nomenclature) {
                // Определяем количество в зависимости от единицы измерения
                if (in_array($nomenclature->unit_of_measurement, ['шт.', 'упак.', 'компл.', 'ящ.', 'пач.'])) {
                    $quantity = rand(1, 10);
                } else {
                    $quantity = rand(1, 10) + round(rand(0, 99) / 100, 2);
                }
                
                // Рассчитываем цену этой номенклатуры для продукта
                $price = $nomenclature->price_per_unit * $quantity;
                
                // Добавляем к общей стоимости продукта
                $totalPrice += $price;
                
                // Создаем связь между продуктом и номенклатурой
                Products_Nomenclature::create([
                    'product_id' => $product->id,
                    'nomenclature_id' => $nomenclature->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Обновляем общую стоимость продукта
            $product->total_price = $totalPrice;
            $product->save();
        }
    }
} 