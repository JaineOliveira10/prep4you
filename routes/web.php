<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PriceTableController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DistributionCenterController;
use App\Http\Controllers\ShipmentController;

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::middleware(['auth'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', function () {
        return view('dashboards.dashboard');
    })->name('dashboard');

    Route::middleware(['restrict.admin'])->group(function () {
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::get('/products/{product}/copy', [ProductController::class, 'copy'])->name('products.copy');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });

    Route::resource('products', ProductController::class)->except(['create', 'store', 'destroy']);

    Route::resource('shipments', ShipmentController::class);
    Route::post('/shipments/calculate-collection-date', [ShipmentController::class, 'calculateCollectionDate'])->name('shipments.calculate-collection-date');

    Route::middleware(['restrict.client'])->group(function () {

        Route::resource('users', UserController::class)->except(['show']);

        Route::resource('price-tables', PriceTableController::class);

        Route::resource('distribution-centers', DistributionCenterController::class);
    });

    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::patch('/users/{user}/update-password', [UserController::class, 'updatePassword'])->name('users.update-password');
    Route::patch('/users/{user}/update-name', [UserController::class, 'updateName'])->name('users.update-name');

    Route::prefix('menu-style')->group(function () {
        Route::get('horizontal', [HomeController::class, 'horizontal'])->name('menu-style.horizontal');
        Route::get('dual-horizontal', [HomeController::class, 'dualhorizontal'])->name('menu-style.dualhorizontal');
    });
});
