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
        $supply->update($request->all());
        return response()->json(['status' => 'Поставка успешно обновлена', 'supply' => $supply]);
    }

    public function delete(Request $request)
    {
        Supplies::destroy($request->supplyId);
        return response()->json(['status' => 'Поставка успешно удалена']);
    }
}
