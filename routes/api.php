<?php

use App\Http\Controllers\v1\User;
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
            Route::post('login', 'store')->name('login');
        });

        // User route
        Route::controller(User\UserController::class)->group(function (): void {
            Route::get('', 'show')->name('show')->middleware(['auth:api', 'user.type:user']);
        });
    });
});
