<?php

namespace App\Http\Controllers;

use App\Models\UnitOfMeasurement;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UnitOfMeasurementController extends Controller
{
    /**
     * Получить список всех единиц измерения.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll()
    {
        $units = UnitOfMeasurement::all();
        
        return response()->json([
            'units' => $units
        ]);
    }

    /**
     * Сохранить новую единицу измерения.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:units_of_measurement',
            'type' => 'required|in:integer,decimal',
            'step' => 'required|numeric|min:0.0001',
            'min_value' => 'required|numeric|min:0',
        ]);

        $unit = UnitOfMeasurement::create($validated);

        return response()->json([
            'status' => 'Единица измерения успешно добавлена',
            'unit' => $unit
        ]);
    }

    /**
     * Удалить единицу измерения.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $unit = UnitOfMeasurement::findOrFail($id);
        
        // Проверяем, используется ли эта единица измерения в номенклатурах
        $inUse = $unit->nomenclatures()->count() > 0;
        
        if ($inUse) {
            return response()->json([
                'status' => 'Ошибка! Эта единица измерения используется в номенклатуре и не может быть удалена.'
            ], 400);
        }
        
        $unit->delete();
        
        return response()->json([
            'status' => 'Единица измерения успешно удалена'
        ]);
    }
} 