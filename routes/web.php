<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalculatorController;
use App\Http\Controllers\DailyBiasController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\SymbolController;
use App\Http\Controllers\TradeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');
    Route::get('analytics', [DashboardController::class, 'index'])->name('analytics');

    Route::patch('trades/{trade}/prices', [TradeController::class, 'updatePrices'])->name('trades.prices.update');
    Route::resource('trades', TradeController::class);
    Route::post('trades/{trade}/images', [TradeController::class, 'uploadImage'])->name('trades.images.store');
    Route::resource('daily-biases', DailyBiasController::class)
        ->parameters(['daily-biases' => 'dailyBias'])
        ->except(['show']);
    Route::get('position-size-calculator', [CalculatorController::class, 'index'])->name('calculator');
    Route::resource('symbols', SymbolController::class)->except(['show', 'create', 'edit']);
    Route::resource('setups', SetupController::class)->except(['show', 'create', 'edit']);

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});
