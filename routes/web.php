<?php

use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\LocaleSwitchController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/', HomeController::class)->name('home');
    Route::get('/language/{locale}', LocaleSwitchController::class)->name('language.switch');
});
