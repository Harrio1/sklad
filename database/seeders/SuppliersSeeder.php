<?php

namespace Database\Seeders;

use App\Models\Suppliers;
use Illuminate\Database\Seeder;

class SuppliersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'ООО "МеталлПром"',
                'address' => 'г. Москва, ул. Промышленная, 15',
                'comments' => 'Поставщик металлических изделий',
                'phone' => '+7 (495) 123-45-67',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'ЗАО "ПластикТрейд"',
                'address' => 'г. Санкт-Петербург, ул. Литейная, 42',
                'comments' => 'Поставщик пластиковых компонентов',
                'phone' => '+7 (812) 765-43-21',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'ИП Смирнов А.В.',
                'address' => 'г. Екатеринбург, пр-т Ленина, 78',
                'comments' => 'Мелкооптовый поставщик расходных материалов',
                'phone' => '+7 (343) 234-56-78',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'ООО "ЭлектроКомпонент"',
                'address' => 'г. Новосибирск, ул. Техническая, 23',
                'comments' => 'Поставщик электротехнических изделий',
                'phone' => '+7 (383) 876-54-32',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'АО "СтройМатериалы"',
                'address' => 'г. Краснодар, ул. Строителей, 105',
                'comments' => 'Крупный поставщик строительных материалов',
                'phone' => '+7 (861) 345-67-89',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($suppliers as $supplier) {
            Suppliers::create($supplier);
        }
    }
} 