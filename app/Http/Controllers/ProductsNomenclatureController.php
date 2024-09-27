<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Products;
use App\Models\Nomenclatures;
use App\Models\Products_Nomenclature;

class ProductsNomenclatureController extends Controller
{
    function getProductById(Request $request) {
        $id = $request->id;

        $product = Products::find($id);
        $nomenclatures = Nomenclatures::all();
        //$productNomenclatures = $product->nomenclatures;
        if (!$product) {
            return Inertia::render('Products');
        } else {
            return Inertia::render('ProductsNomenclatures', ['product' => $product, 'nomenclatures' => $nomenclatures ]);
        }
    }

    function addNewProductNomenclature(Request $request) {

        $validated = $request->validate([
            'product' => ['required', 'numeric'],
            'nomenclature' => ['required','numeric'],
            'count' => ['required','numeric'],
        ]);
        
           if ($validated){
                $product = new Products_Nomenclature;
                $product->product_id = $request->product;
                $product->nomenclature_id = $request->nomenclature;
                $product->quantity = $request->count;
                $product->save();
                return Response::json(['status' => 'Данные добавлены', 'isOk' => True], 200);
           } else {
                return Response::json(['status' => 'Ошибка формата входных данных', 'isOk' => False], 200);
           }


    }

}
