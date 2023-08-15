<?php

use App\Http\Controllers\v1\User;
use App\Http\Controllers\v1\Order;
use App\Http\Controllers\v1\Payment;
use Illuminate\Support\Facades\Route;

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

Route::name('v1:')->prefix('v1')->group(function (): void {
    /** User Routes */
    Route::name('user:')->prefix('user')->group(function (): void {
        // Authentication
        Route::controller(User\LoginController::class)->group(function (): void {
            Route::post('login', 'store')->name('login')->middleware(['guest:api']);
        });

        // User route
        Route::controller(User\UserController::class)->group(function (): void {
            Route::get('', 'show')->name('show')->middleware(['auth:api', 'user.type:user']);
        });

        // User payments
        Route::get('payments', User\PaymentController::class)->name('payments:index')->middleware(['auth:api']);

        // User orders
        Route::get('orders', User\OrderController::class)->name('orders:index')->middleware(['auth:api']);
    });

    // Payments management
    Route::name('payments:')->prefix('payments')
        ->controller(Payment\PaymentController::class)->group(function (): void {
            Route::get('', 'index')->middleware(['auth:api', 'user.type:admin'])->name('index');
            Route::post('', 'store')->middleware(['auth:api'])->name('store');
            Route::get('/{payment:uuid}', 'show')->middleware(['auth:api'])->name('show');
            Route::delete('/{payment:uuid}', 'destroy')->middleware(['auth:api'])->name('delete');
        });

    // Order management
    Route::name('orders:')->prefix('orders')
        ->controller(Order\OrderController::class)->group(function (): void {
            Route::post('', 'store')->middleware(['auth:api'])->name('store');
            Route::get('/{order:uuid}', 'show')->middleware(['auth:api'])->name('show');
        });
});
