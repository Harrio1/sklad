<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\ProductsNomenclatureController;
use App\Http\Controllers\Api\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/products', [ProductsController::class, 'getProducts'])->name('api.products.index');
Route::post('/orders', [OrdersController::class, 'placeOrder'])->name('api.orders.place');
Route::get('/orders', [OrdersController::class, 'getOrders']);
Route::put('/orders/{order}', [OrdersController::class, 'updateOrderStatus']);
Route::post('/orders/{order}/status', [OrdersController::class, 'updateStatus']);
Route::get('/products/{productId}/nomenclatures', [ProductsNomenclatureController::class, 'getNomenclaturesByProductId']);

// Маршруты для дашборда
Route::prefix('dashboard')->group(function () {
    Route::get('/products', [DashboardController::class, 'products']);
    Route::get('/suppliers', [DashboardController::class, 'suppliers']);
    Route::get('/supplies', [DashboardController::class, 'supplies']);
    Route::get('/nomenclature', [DashboardController::class, 'nomenclature']);
    Route::get('/product-nomenclature', [DashboardController::class, 'productNomenclature']);
    Route::get('/orders', [DashboardController::class, 'orders']);
});