<?php

namespace Database\Seeders;

use App\Models\Nomenclatures;
use App\Models\Supplies;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SuppliesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Получаем все номенклатуры
        $nomenclatures = Nomenclatures::all();
        
        if ($nomenclatures->isEmpty()) {
            $this->command->info('Добавьте номенклатуры перед запуском этого сидера!');
            return;
        }
        
        // Создаем историю поставок за последние 30 дней
        $startDate = Carbon::now()->subDays(30);
        
        foreach ($nomenclatures as $nomenclature) {
            // Добавляем от 2 до 5 поставок для каждой номенклатуры
            $suppliesCount = rand(2, 5);
            
            for ($i = 0; $i < $suppliesCount; $i++) {
                // Рандомная дата в пределах последних 30 дней
                $supplyDate = $startDate->copy()->addDays(rand(0, 30));
                
                // Рандомное количество, зависящее от единицы измерения
                if (in_array($nomenclature->unit_of_measurement, ['шт.', 'упак.', 'компл.', 'ящ.', 'пач.'])) {
                    // Для целочисленных единиц
                    $quantity = rand(10, 100);
                } else {
                    // Для дробных единиц
                    $quantity = rand(10, 100) + round(rand(0, 99) / 100, 2);
                }
                
                // Цена за единицу (используем цену из номенклатуры, но добавляем небольшую вариацию)
                $price = $nomenclature->price_per_unit * (1 + (rand(-5, 5) / 100));
                
                Supplies::create([
                    'nomenclature_id' => $nomenclature->id,
                    'supply_date' => $supplyDate,
                    'quantity' => $quantity,
                    'unit' => $nomenclature->unit_of_measurement,
                    'price' => $price,
                    'created_at' => $supplyDate,
                    'updated_at' => $supplyDate,
                ]);
            }
        }
    }
} 