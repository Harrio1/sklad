<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Models\Products;
use App\Models\Nomenclatures;
use App\Models\Products_Nomenclature;
use Inertia\Inertia;

class ProductsNomenclatureController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'nomenclatures' => 'required|array',
            'nomenclatures.*.id' => 'required|exists:nomenclatures,id',
            'nomenclatures.*.quantity' => 'required|numeric|min:0',
            'nomenclatures.*.price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product = Products::findOrFail($request->product_id);

        foreach ($request->nomenclatures as $nomenclature) {
            if ($nomenclature['id'] && $nomenclature['quantity'] > 0) {
                $product->nomenclatures()->attach($nomenclature['id'], [
                    'quantity' => $nomenclature['quantity'],
                    'price' => $nomenclature['price'],
                ]);
            }
        }

        return response()->json(['status' => 'Данные добавлены'], 200);
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

    public function getAvailableNomenclatures(Request $request)
    {
        // Получаем все номенклатуры без фильтрации
        $availableNomenclatures = Nomenclatures::all();

        return Response::json(['nomenclatures' => $availableNomenclatures], 200);
    }

    public function index()
    {
        $productsNomenclatures = Products_Nomenclature::with(['product', 'nomenclature'])->get();
        
        $formattedData = $productsNomenclatures->groupBy('product_id')->map(function ($group) {
            $product = $group->first()->product;
            $nomenclatures = $group->map(function ($item) {
                return [
                    'id' => $item->nomenclature->id,
                    'name' => $item->nomenclature->name,
                    'pivot' => [
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                    ],
                ];
            });
            
            return [
                'id' => $product->id,
                'name' => $product->name,
                'nomenclatures' => $nomenclatures,
            ];
        })->values();

        return Inertia::render('ProductsNomenclatures', [
            'productsNomenclatures' => $formattedData
        ]);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'nomenclature_id' => 'required|exists:nomenclatures,id',
        ]);

        $product = Products::findOrFail($request->product_id);
        $deleted = $product->nomenclatures()->detach($request->nomenclature_id);

        if ($deleted) {
            return Response::json(['status' => 'Связь успешно удалена'], 200);
        } else {
            return Response::json(['status' => 'Не удалось удалить связь'], 400);
        }
    }
}
