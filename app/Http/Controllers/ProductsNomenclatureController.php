<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\Products;
use App\Models\Nomenclatures;

class ProductsNomenclatureController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'nomenclatures' => 'required|array',
            'nomenclatures.*.id' => 'required|exists:nomenclatures,id',
            'nomenclatures.*.quantity' => 'required|numeric|min:0',
            'nomenclatures.*.price' => 'required|numeric|min:0',
        ]);

        $product = Products::findOrFail($request->product_id);

        foreach ($request->nomenclatures as $nomenclature) {
            $product->nomenclatures()->attach($nomenclature['id'], [
                'quantity' => $nomenclature['quantity'],
                'price' => $nomenclature['price'],
            ]);
        }

        return Response::json(['status' => 'Данные добавлены'], 200);
    }

    public function getProductsNomenclatures()
    {
        $products = Products::with('nomenclatures')->get();
        $nomenclatures = Nomenclatures::all();

        return Response::json([
            'products' => $products,
            'nomenclatures' => $nomenclatures,
        ], 200);
    }

    public function deleteById(Request $request)
    {
        $product = Products::findOrFail($request->product_id);
        $product->nomenclatures()->detach($request->nomenclature_id);

        return Response::json(['status' => 'Связь успешно удалена'], 200);
    }
}
