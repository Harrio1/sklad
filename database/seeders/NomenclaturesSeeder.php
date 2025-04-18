<?php

namespace Database\Seeders;

use App\Models\Nomenclatures;
use App\Models\Suppliers;
use App\Models\UnitOfMeasurement;
use Illuminate\Database\Seeder;

class NomenclaturesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Получаем всех поставщиков и единицы измерения для использования их в номенклатуре
        $suppliers = Suppliers::all();
        $units = UnitOfMeasurement::all()->pluck('name')->toArray();
        
        if ($suppliers->isEmpty()) {
            $this->command->info('Добавьте поставщиков перед запуском этого сидера!');
            return;
        }
        
        if (empty($units)) {
            $units = ['шт.', 'кг.', 'л.', 'м.', 'м²', 'упак.'];
        }
        
        $nomenclatures = [
            [
                'name' => 'Болт M10',
                'price_per_unit' => 5.50,
                'unit_of_measurement' => 'шт.',
                'total_quantity' => 1500,
                'total_price' => 8250,
            ],
            [
                'name' => 'Гайка M10',
                'price_per_unit' => 3.20,
                'unit_of_measurement' => 'шт.',
                'total_quantity' => 2000,
                'total_price' => 6400,
            ],
            [
                'name' => 'Труба металлическая 15мм',
                'price_per_unit' => 120.50,
                'unit_of_measurement' => 'м.',
                'total_quantity' => 50,
                'total_price' => 6025,
            ],
            [
                'name' => 'Лист металлический 2мм',
                'price_per_unit' => 850.00,
                'unit_of_measurement' => 'м²',
                'total_quantity' => 25,
                'total_price' => 21250,
            ],
            [
                'name' => 'Шайба M10',
                'price_per_unit' => 1.50,
                'unit_of_measurement' => 'шт.',
                'total_quantity' => 3000,
                'total_price' => 4500,
            ],
            [
                'name' => 'Краска белая фасадная',
                'price_per_unit' => 350.00,
                'unit_of_measurement' => 'л.',
                'total_quantity' => 30,
                'total_price' => 10500,
            ],
            [
                'name' => 'Цемент М500',
                'price_per_unit' => 420.00,
                'unit_of_measurement' => 'кг.',
                'total_quantity' => 200,
                'total_price' => 84000,
            ],
            [
                'name' => 'Шуруп 5x50мм',
                'price_per_unit' => 2.20,
                'unit_of_measurement' => 'шт.',
                'total_quantity' => 2500,
                'total_price' => 5500,
            ],
            [
                'name' => 'Провод электрический 2.5мм²',
                'price_per_unit' => 45.80,
                'unit_of_measurement' => 'м.',
                'total_quantity' => 100,
                'total_price' => 4580,
            ],
            [
                'name' => 'Изолента',
                'price_per_unit' => 45.00,
                'unit_of_measurement' => 'шт.',
                'total_quantity' => 50,
                'total_price' => 2250,
            ],
            [
                'name' => 'Гвозди 100мм',
                'price_per_unit' => 180.00,
                'unit_of_measurement' => 'кг.',
                'total_quantity' => 10,
                'total_price' => 1800,
            ],
            [
                'name' => 'Клей ПВА строительный',
                'price_per_unit' => 290.00,
                'unit_of_measurement' => 'л.',
                'total_quantity' => 15,
                'total_price' => 4350,
            ],
            [
                'name' => 'Саморезы по дереву 3.5x35мм',
                'price_per_unit' => 0.80,
                'unit_of_measurement' => 'шт.',
                'total_quantity' => 5000,
                'total_price' => 4000,
            ],
            [
                'name' => 'Профиль металлический 60x27мм',
                'price_per_unit' => 145.00,
                'unit_of_measurement' => 'м.',
                'total_quantity' => 80,
                'total_price' => 11600,
            ],
            [
                'name' => 'Герметик силиконовый',
                'price_per_unit' => 180.00,
                'unit_of_measurement' => 'шт.',
                'total_quantity' => 25,
                'total_price' => 4500,
            ],
        ];
        
        // Создаем номенклатуры
        foreach ($nomenclatures as $item) {
            Nomenclatures::create([
                'name' => $item['name'],
                'supplier_id' => $suppliers->random()->id, // Рандомный поставщик
                'price_per_unit' => $item['price_per_unit'],
                'unit_of_measurement' => $item['unit_of_measurement'],
                'total_quantity' => $item['total_quantity'],
                'total_price' => $item['total_price'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
} 