<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Suppliers;


class SuppliersController extends Controller
{
    public function store(Request $request)
    {
    
        
        $validated = $request->validate([
            'supplierName' => ['required', 'max:50'],
            'address' => ['required', 'max:100'],
            'supplierComments' => ['required', 'max:500'],
            'phoneNumber' => ['required', 'max:11','min:8','integer'],
        ]);

        $suppliers = new Suppliers;

        $suppliers->supplierName = $request->supplierName;
        $suppliers->address = $request->address;
        $suppliers->supplierComments = $request->supplierComments;
        $suppliers->phoneNumber = $request->phoneNumber;



        //dd($suppliers);

        $suppliers->save();

        return Redirect::route('suppliers');
    }
}
