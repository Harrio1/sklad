<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Products;

class ProductsNomenclatureController extends Controller
{
    function getProductById(Request $request) {
        $id = $request->id;

        
        $product = Products::find($id);
        
      

        if (!$product) {
            return Inertia::render('Products');
        } else {
            return Inertia::render('ProductsNomenclatures', ['product' => $product ]);
        }

        
    }
}
