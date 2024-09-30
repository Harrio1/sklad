<?php

namespace App\Http\Controllers;



use App\Models\Supplies;
use App\Models\Nomenclatures;
use Illuminate\Http\Request;

class SuppliesController extends Controller
{
    public function getSupplies()
    {
        $supplies = Supplies::with('nomenclature')->get();
        return response()->json(['supplies' => $supplies]);
    }

    public function getNomenclatures()
    {
        $nomenclatures = Nomenclatures::all();
        return response()->json(['nomenclatures' => $nomenclatures]);
    }

    public function store(Request $request)
    {
        $supply = Supplies::create($request->all());
        return response()->json(['status' => 'Поставка успешно добавлена', 'supply' => $supply]);
    }

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

    public function delete(Request $request)
    {
        Supplies::destroy($request->supplyId);
        return response()->json(['status' => 'Поставка успешно удалена']);
    }
}
