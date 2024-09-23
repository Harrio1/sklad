<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\SuppliersController;
use App\Http\Controllers\NomenclaturesController;

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
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/suppliers', function () {
        return Inertia::render('Suppliers');
    })->name('suppliers');
    Route::post('/add-suppliers', [SuppliersController::class, 'store'])->name('add-suppliers');
    Route::get('/get-suppliers', [SuppliersController::class, 'getSuppliers'])->name('get-suppliers');
    Route::post('/delete-suppliers', [SuppliersController::class, 'deleteById'])->name('delete-suppliers');
    Route::post('/update-suppliers', [SuppliersController::class, 'updateById'])->name('update-suppliers');

    Route::get('/supplies', function () {
        return Inertia::render('Supplies');
    })->name('supplies');

    Route::get('/nomenclature', function () {
        return Inertia::render('Nomenclature');
    })->name('nomenclature');
    Route::post('/add-nomenclature', [NomenclaturesController::class, 'store'])->name('add-nomenclature');
    Route::get('/get-nomenclature', [NomenclaturesController::class, 'getNomenclatures'])->name('get-nomenclature');
    Route::post('/delete-nomenclature', [NomenclaturesController::class, 'deleteById'])->name('delete-nomenclature');
    Route::post('/update-nomenclature', [NomenclaturesController::class, 'updateById'])->name('update-nomenclature');
});
