<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Frontend\Home as FrontendHome;

Route::get('/', FrontendHome::class)->name('home');



Route::get('/language/{locale}', function ($locale) {
    if (in_array($locale, config('app.locales'))) {
        session(['locale' => $locale]);
    }
    return back();
})->name('language.switch');
