<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\Nomenclatures;

class NomenclaturesController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:50'],
            'supplier_id' => ['required', 'integer'],
            'price_per_unit' => ['required', 'numeric', 'min:0'],
            'unit_of_measurement' => ['required', 'in:шт.,кг.,л.'], // Новое правило валидации
        ]);

        $nomenclature = new Nomenclatures;

        $nomenclature->name = $request->name;
        $nomenclature->supplier_id = $request->supplier_id;
        $nomenclature->price_per_unit = $request->price_per_unit;
        $nomenclature->unit_of_measurement = $request->unit_of_measurement; // Новое поле
        $nomenclature->total_quantity = 0;
        $nomenclature->total_price = 0;

        $nomenclature->save();

        return Response::json(['status' => 'Данные добавлены'], 200);
    }

    public function getNomenclatures()
    {
        $nomenclatures = Nomenclatures::with('supplier')->get();
        return Response::json(['nomenclatures' => $nomenclatures], 200);
    }

    public function deleteById(Request $request)
    {
        $nomenclature = Nomenclatures::find($request->nomenclature_id);
        if (!$nomenclature) {
            return Response::json(['status' => 'Ошибка, элемент не найден'], 404);
        }

        // Удаление связанных записей в таблице supplies
        $nomenclature->supplies()->delete(); // Предполагается, что у вас есть связь supplies в модели Nomenclatures

        $nomenclature->delete();
        return Response::json(['status' => 'Номенклатура успешно удалена'], 200);
    }
}
