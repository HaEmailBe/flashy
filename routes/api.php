<?php

use App\Http\Controllers\API\LinksController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['api.key', 'throttle:api-key'])->group(function () {
    Route::apiResources([
        '/links' => LinksController::class
    ]);
});

Route::get('/r/{slug}', [LinksController::class, 'redirect'])->name('slug.redirect');

Route::get('/links/{slug}/stats', [LinksController::class, 'statistics'])->name('slug.statistics');

