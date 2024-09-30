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
        ]);

        $nomenclatures = new Nomenclatures;

        $nomenclatures->name = $request->name;
        $nomenclatures->supplier_id = $request->supplier_id;

        $nomenclatures->save();

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
    $nomenclature->delete();
    return Response::json(['status' => 'Номенклатура успешно удалена'], 200);
    }
}
