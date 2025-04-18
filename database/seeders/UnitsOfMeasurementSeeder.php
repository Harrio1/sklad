<?php

namespace Database\Seeders;

use App\Models\UnitOfMeasurement;
use Illuminate\Database\Seeder;

class UnitsOfMeasurementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            [
                'name' => 'шт.',
                'type' => 'integer',
                'step' => 1,
                'min_value' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'кг.',
                'type' => 'decimal',
                'step' => 0.01,
                'min_value' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'л.',
                'type' => 'decimal',
                'step' => 0.01,
                'min_value' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'м.',
                'type' => 'decimal',
                'step' => 0.01,
                'min_value' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'м²',
                'type' => 'decimal',
                'step' => 0.01,
                'min_value' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'м³',
                'type' => 'decimal',
                'step' => 0.01,
                'min_value' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'пач.',
                'type' => 'integer',
                'step' => 1,
                'min_value' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'упак.',
                'type' => 'integer',
                'step' => 1,
                'min_value' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'ящ.',
                'type' => 'integer',
                'step' => 1,
                'min_value' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'компл.',
                'type' => 'integer',
                'step' => 1,
                'min_value' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($units as $unit) {
            UnitOfMeasurement::create($unit);
        }
    }
} 