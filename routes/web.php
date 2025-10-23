<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PriceTableController;

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::resource('users', UserController::class)->middleware(['auth', 'restrict.client'])->except(['show']);
Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show')->middleware('auth');
Route::patch('/users/{user}/update-password', [UserController::class, 'updatePassword'])->name('users.update-password')->middleware('auth');

Route::resource('price-tables', PriceTableController::class)->middleware(['auth', 'restrict.client']);

Route::get('/', function () {
    return view('dashboards.dashboard');
})->middleware('auth')->name('dashboard');

//App Details Page => 'Dashboard'], function() {
Route::group(['prefix' => 'menu-style'], function() {
    //MenuStyle Page Routs
    Route::get('horizontal', [HomeController::class, 'horizontal'])->name('menu-style.horizontal');
    Route::get('dual-horizontal', [HomeController::class, 'dualhorizontal'])->name('menu-style.dualhorizontal');
});




