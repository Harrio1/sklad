<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('nomenclatures', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->decimal('price_per_unit', 12, 2);
            $table->decimal('total_quantity', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);
            $table->enum('unit_of_measurement', ['шт.', 'кг.', 'л.']); // Новая колонка
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nomenclatures');
    }
};
