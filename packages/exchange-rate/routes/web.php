<?php

use Illuminate\Support\Facades\Route;
use AlhajiAki\ExchangeRate\ExchangeRateController;

Route::get('exchange-rate', ExchangeRateController::class)->name('exchange-rate:index');
