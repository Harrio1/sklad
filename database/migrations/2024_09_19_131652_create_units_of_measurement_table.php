<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('units_of_measurement', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['integer', 'decimal'])->default('integer');
            $table->decimal('step', 8, 4)->default(1);
            $table->decimal('min_value', 8, 4)->default(0);
            $table->timestamps();
        });

        DB::table('units_of_measurement')->insert([
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
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units_of_measurement');
    }
}; 