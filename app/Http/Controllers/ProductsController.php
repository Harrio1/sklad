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
                $product->save();
                return Response::json(['status' => 'Данные добавлены', 'isOk' => True], 200);
           } else {
                return Response::json(['status' => 'Ошибка формата входных данных', 'isOk' => False], 200);
           }
           
      
    }


    public function updateById(Request $request){

        $flight = Products::findOr($request->id, function () {
            return Response::json(['status' => 'Ошибка, элемент не найден', 'isOk' => False], 200);
        });
        
        $validated = $request->validate([
            'name' => ['required', 'max:50'],
            'price' => ['required','numeric'],
        ]);
        if ($validated){
            $flight->name = $request->name;
            $flight->price = $request->price;
            $flight->save();
            return Response::json(['status' => 'Продукт успешно изменен', 'isOk' => True], 200);

        } else {
            return Response::json(['status' => 'Неверные данные.','isOk' => False], 200);
        }
           
    }


    public function deleteById(Request $request){

        $flight = Products::findOr($request->id, function () {
              return Response::json(['status' => 'Ошибка, элемент не найден'], 200);
        });
            Products::destroy($request->id);
            return Response::json(['status' => 'Продукт успешно удален'], 200);
    }



}
