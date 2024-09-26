<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Products;
use Illuminate\Support\Facades\Response;

class ProductsController extends Controller
{
    public function getProducts(){

        $products = Products::all();
        return Response::json(['products' => $products], 200);

    } 

    public function store(Request $request)
    {
    
        
        $validated = $request->validate([
            'name' => ['required', 'max:50'],
            'price' => ['required','numeric'],
        ]);
           if ($validated){
                $product = new Products;
                $product->name = $request->name;
                $product->price = $request->price;
                $suppliers->save();
                return Response::json(['status' => 'Данные добавлены', 'isOk' => True], 200);
           } else {
                return Response::json(['status' => 'Ошибка формата входных данных', 'isOk' => False], 200);
           }
           
      
    }

}
