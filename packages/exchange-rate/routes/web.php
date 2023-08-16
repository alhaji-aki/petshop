<?php

use AlhajiAki\ExchangeRate\ExchangeRateController;
use Illuminate\Support\Facades\Route;

Route::get('exchange-rate', ExchangeRateController::class)->name('exchange-rate:index');
