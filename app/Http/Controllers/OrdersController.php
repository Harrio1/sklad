<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orders;
use App\Models\Products;

class OrdersController extends Controller
{
    public function placeOrder(Request $request)
    {
        $orderItems = $request->input('items');
        $insufficientNomenclatures = [];

        foreach ($orderItems as $item) {
            $product = Products::with('nomenclatures')->find($item['id']);
            foreach ($product->nomenclatures as $nomenclature) {
                $requiredQuantity = $nomenclature->pivot->quantity * $item['quantity'];
                if ($nomenclature->total_quantity < $requiredQuantity) {
                    $insufficientNomenclatures[] = [
                        'id' => $nomenclature->id,
                        'name' => $nomenclature->name,
                        'required' => $requiredQuantity,
                        'available' => $nomenclature->total_quantity,
                    ];
                }
            }
        }

        if (!empty($insufficientNomenclatures)) {
            return response()->json([
                'status' => 'Недостаточно материалов',
                'insufficient' => $insufficientNomenclatures
            ], 400);
        }

        // Создание нового заказа
        $order = new Orders();
        $order->products = json_encode($orderItems);
        $order->status = 1; // В процессе
        $order->save();

        return response()->json(['status' => 'Заказ успешно размещен'], 200);
    }
}