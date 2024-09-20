<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\SuppliersController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

// dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');


// suppliers
    Route::get('/suppliers', function () {
        return Inertia::render('Suppliers');
    })->name('suppliers');
    Route::post('/add-suppliers', [SuppliersController::class, 'store'])->name('add-suppliers');
    Route::get('/get-suppliers', [SuppliersController::class, 'getSuppliers'])->name('get-suppliers');
    Route::post('/delete-suppliers', [SuppliersController::class, 'deleteById'])->name('delete-suppliers');
    Route::post('/update-suppliers', [SuppliersController::class, 'updateById'])->name('update-suppliers');


// supplies
    Route::get('/supplies', function () {
        return Inertia::render('Supplies');
    })->name('supplies');


// nomenclatures
    Route::get('/nomenclature', function () {
        return Inertia::render('Nomenclature');
    })->name('nomenclature');




});
