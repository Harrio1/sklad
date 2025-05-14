<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Products;
use App\Models\Suppliers;
use App\Models\Supplies;
use App\Models\Nomenclatures;
use App\Models\Products_Nomenclature;
use App\Models\Orders;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Получение статистики по продуктам
     */
    public function products()
    {
        try {
            $count = Products::count();
            $recentItems = Products::latest()->take(3)->get(['id', 'name']);

            return response()->json([
                'success' => true,
                'data' => [
                    'count' => $count,
                    'recentItems' => $recentItems
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Получение статистики по поставщикам
     */
    public function suppliers()
    {
        try {
            $count = Suppliers::count();
            $recentItems = Suppliers::latest()->take(3)->get(['id', 'name', 'phone']);

            return response()->json([
                'success' => true,
                'data' => [
                    'count' => $count,
                    'recentItems' => $recentItems
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Получение статистики по поставкам
     */
    public function supplies()
    {
        try {
            $count = Supplies::count();
            $recentItems = Supplies::with('nomenclature')
                ->latest()
                ->take(3)
                ->get()
                ->map(function ($supply) {
                    return [
                        'id' => $supply->id,
                        'nomenclature' => $supply->nomenclature->name ?? 'Без имени',
                        'quantity' => $supply->quantity . ' ' . $supply->unit,
                        'date' => $supply->supply_date
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'count' => $count,
                    'recentItems' => $recentItems
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Получение статистики по номенклатуре
     */
    public function nomenclature()
    {
        try {
            $count = Nomenclatures::count();
            $recentItems = Nomenclatures::latest()->take(3)->get(['id', 'name', 'unit_of_measurement as unit']);

            return response()->json([
                'success' => true,
                'data' => [
                    'count' => $count,
                    'recentItems' => $recentItems
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Получение статистики по номенклатуре продуктов
     */
    public function productNomenclature()
    {
        try {
            $count = Products_Nomenclature::count();
            $recentItems = Products_Nomenclature::with(['product', 'nomenclature'])
                ->latest()
                ->take(3)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product' => $item->product->name ?? 'Без имени',
                        'nomenclature' => $item->nomenclature->name ?? 'Без имени',
                        'quantity' => $item->quantity . ' ' . ($item->nomenclature->unit_of_measurement ?? '')
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'count' => $count,
                    'recentItems' => $recentItems
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Получение статистики по заказам
     */
    public function orders()
    {
        // Получаем общее количество заказов
        $count = Orders::count();

        // Получаем последние 6 заказов (можно изменить лимит)
        $recentItems = Orders::orderBy('created_at', 'desc')
            ->take(6)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'name' => 'Заказ №' . $order->id,
                    'count' => $order->items_count ?? 0, // если есть поле с количеством товаров
                    'date' => $order->created_at ? $order->created_at->format('Y-m-d') : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $count,
                'recentItems' => $recentItems,
            ]
        ]);
    }
} 