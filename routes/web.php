<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PriceTableController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DistributionCenterController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\ShipmentPdfController;
use App\Http\Controllers\ProductLabelController;

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::middleware(['auth'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', function () {
        return view('dashboards.dashboard');
    })->name('dashboard');

    Route::middleware(['restrict.client'])->group(function () {

        Route::resource('users', UserController::class)->except(['show']);

        Route::resource('price-tables', PriceTableController::class);

        Route::resource('distribution-centers', DistributionCenterController::class);

        Route::get('shipments/manage-shipments', [ShipmentController::class, 'manageShipments'])->name('shipments.manage-shipments');
    });

    Route::middleware(['restrict.admin'])->group(function () {
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::get('/products/{product}/copy', [ProductController::class, 'copy'])->name('products.copy');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::post('/products/ajax', [ProductController::class, 'storeAjax'])->name('products.store.ajax');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        
        Route::resource('shipments', ShipmentController::class)->except(['manageShipments']);
        Route::post('/shipments/calculate-collection-date', [ShipmentController::class, 'calculateCollectionDate'])->name('shipments.calculate-collection-date');
        Route::post('/shipments/get-products-by-client', [ShipmentController::class, 'getProductsByClient'])->name('shipments.get-products-by-client');
        Route::post('/shipments/get-product-price', [ShipmentController::class, 'getProductPrice'])->name('shipments.get-product-price');

        Route::post('shipments/import', [ShipmentController::class, 'import'])->name('shipments.import');
        Route::post('shipments/preview', [ShipmentController::class, 'preview'])->name('shipments.preview');
        Route::post('shipments/{shipment}/pdf', [ShipmentPdfController::class, 'upload'])->name('shipments.pdf.upload');
        Route::delete('shipment-pdfs/{pdf}', [ShipmentPdfController::class, 'destroy'])->name('shipments.pdf.destroy');
    });

    Route::resource('products', ProductController::class)->except(['create', 'store', 'destroy']);
    Route::get('/products/{product}/json', [ProductController::class, 'getJson'])->name('products.json');



    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::patch('/users/{user}/update-password', [UserController::class, 'updatePassword'])->name('users.update-password');
    Route::patch('/users/{user}/update-name', [UserController::class, 'updateName'])->name('users.update-name');

    Route::prefix('menu-style')->group(function () {
        Route::get('horizontal', [HomeController::class, 'horizontal'])->name('menu-style.horizontal');
        Route::get('dual-horizontal', [HomeController::class, 'dualhorizontal'])->name('menu-style.dualhorizontal');
    });

    Route::post('/products/{product}/generate-labels', [ProductLabelController::class, 'generatePdf'])->name('products.labels.generate');
    Route::get('/products/{product}/labels-modal', [ProductLabelController::class, 'showModal'])->name('products.labels.modal');
});
