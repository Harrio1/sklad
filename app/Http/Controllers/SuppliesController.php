<?php

namespace App\Http\Controllers;

use App\Models\Supplies;
use App\Models\Nomenclatures;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Контроллер для управления действиями, связанными с поставками
class SuppliesController extends Controller
{
    // Метод для получения всех поставок
    public function getSupplies()
    {
        $supplies = Supplies::with('nomenclature')->get();
        return response()->json(['supplies' => $supplies]);
    }

    // Метод для получения всех номенклатур
    public function getNomenclatures()
    {
        $nomenclatures = Nomenclatures::all();
        return response()->json(['nomenclatures' => $nomenclatures]);
    }

    // Метод для добавления новой поставки
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomenclature_id' => 'required|exists:nomenclatures,id',
            'supply_date' => 'required|date',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|in:шт.,кг.,л.',
        ]);

        DB::beginTransaction();

        try {
            $nomenclature = Nomenclatures::findOrFail($request->nomenclature_id);

            $supply = new Supplies;
            $supply->nomenclature_id = $request->nomenclature_id;
            $supply->supply_date = $request->supply_date;
            $supply->quantity = $request->quantity;
            $supply->unit = $request->unit;
            $supply->price = $nomenclature->price_per_unit * $request->quantity;
            $supply->save();

            // Обновляем данные в номенклатуре
            $nomenclature->total_quantity += $request->quantity;
            $nomenclature->total_price += $supply->price;
            $nomenclature->save();

            DB::commit();

            return response()->json(['status' => 'Поставка успешно добавлена', 'supply' => $supply]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'Ошибка при добавлении поставки', 'error' => $e->getMessage()], 500);
        }
    }

    // Метод для обновления поставки
    public function update(Request $request)
    {
        $supply = Supplies::find($request->supplyId);

        $flight = Supplies::findOr($request->supplyId, function () {
            return response()->json(['status' => 'Ошибка, элемент не найден']);
        });
            $flight->nomenclature_id = $request->nomenclatureId;
            $flight->supply_date = $request->supplyDate;
            $flight->quantity = $request->quantity;
            $flight->unit = $request->unit;
            $flight->price = $request->price;
            $flight->save();

        return response()->json(['status' => 'Поставка успешно обновлена']);
    }

    // Метод для удаления поставки
    public function delete(Request $request)
    {
        Supplies::destroy($request->supplyId);
        return response()->json(['status' => 'Поставка успешно удалена']);
    }
}
