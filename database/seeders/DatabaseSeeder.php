<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // 1. Поставщики
            SuppliersSeeder::class,
            
            // 2. Единицы измерения
            UnitsOfMeasurementSeeder::class,
            
            // 3. Номенклатура
            NomenclaturesSeeder::class,
            
            // 4. Поставки
            SuppliesSeeder::class,
            
            // 5. Продукты
            ProductsSeeder::class,
            
            // 6. Связь продуктов и номенклатуры
            ProductsNomenclatureSeeder::class,
            
            // 7. Заказы
            OrdersSeeder::class,
        ]);
        
        $this->command->info('База данных успешно заполнена тестовыми данными!');
    }
}
