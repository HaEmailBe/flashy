<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LinksController;

Route::redirect('/', '/login', 301);

Auth::routes();

Route::prefix('admin')->middleware(['auth'])->name('web.')->group(function () {
    Route::resource('/links', LinksController::class);
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::fallback(function () {
    return "<h1>Sorry, page not found</h1>";
});

