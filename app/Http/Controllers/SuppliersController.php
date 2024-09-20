<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Suppliers;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;

class SuppliersController extends Controller
{
    public function store(Request $request)
    {
    
        
        $validated = $request->validate([
            'supplierName' => ['required', 'max:50'],
            'address' => ['required', 'max:100'],
            'supplierComments' => ['required', 'max:500'],
            'phoneNumber' => ['required', 'integer'],
        ]);

        $suppliers = new Suppliers;

        $suppliers->name = $request->supplierName;
        $suppliers->address = $request->address;
        $suppliers->comments = $request->supplierComments;
        $suppliers->phone = $request->phoneNumber;

        //dd($suppliers);

        $suppliers->save();

        return Response::json(['success' => 'Данные добавлены'], 200);
       // return Redirect::route('suppliers',['message' => 'Данные добавлены']);
    }

    public function getSuppliers(){

        $suppliers = Suppliers::all();
        return Response::json(['suppliers' => $suppliers], 200);

    }

    public function deleteById(Request $request){

        $flight = Suppliers::findOr($request->suppliers_id, function () {
              return Response::json(['status' => 'Ошибка, элемент не найден'], 200);
        });
            Suppliers::destroy($request->suppliers_id);
            return Response::json(['status' => 'Поставщик успешно удален'], 200);
    }



    public function updateById(Request $request){

        $flight = Suppliers::findOr($request->supplierId, function () {
              return Response::json(['status' => 'Ошибка, элемент не найден'], 200);
        });
           $flight->name = $request->supplierName;
           $flight->address = $request->address;
           $flight->comments = $request->supplierComments;
           $flight->phone = $request->phoneNumber;
           $flight->save();
           
            return Response::json(['status' => 'Поставщик успешно изменен'], 200);
    }

}
