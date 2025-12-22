<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SellerController;

Route::get('/', [HomeController::class, 'index'])->name('home');



Route::get('/language/{locale}', function ($locale) {
    if (in_array($locale, config('app.locales'))) {
        session(['locale' => $locale]);
    }
    return back();
})->name('language.switch');
