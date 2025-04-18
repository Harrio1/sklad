<?php

namespace Database\Seeders;

use App\Models\Orders;
use App\Models\Products;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Получаем все продукты
        $products = Products::all();
        
        if ($products->isEmpty()) {
            $this->command->info('Добавьте продукты перед запуском этого сидера!');
            return;
        }
        
        // Создаем 10 заказов
        for ($i = 0; $i < 10; $i++) {
            // Выбираем от 1 до 5 случайных продуктов для заказа
            $orderProducts = $products->random(rand(1, 5))->map(function ($product) {
                // Определяем случайное количество каждого продукта от 1 до 3
                $quantity = rand(1, 3);
                
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'quantity' => $quantity,
                    'price' => $product->price,
                    'total_price' => $product->price * $quantity
                ];
            })->values()->toArray();
            
            // Создаем заказ со случайным статусом
            $statusOptions = [1, 2, 3]; // Например: 1-новый, 2-в обработке, 3-завершен
            $status = $statusOptions[array_rand($statusOptions)];
            
            // Создаем случайную дату для заказа в пределах последних 60 дней
            $orderDate = Carbon::now()->subDays(rand(0, 60));
            
            Orders::create([
                'products' => json_encode($orderProducts),
                'status' => $status,
                'created_at' => $orderDate,
                'updated_at' => $orderDate
            ]);
        }
    }
} 