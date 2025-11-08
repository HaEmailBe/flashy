<?php

use App\Http\Controllers\API\LinksController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware(['api.key', 'throttle:api-key'])->group(function () {
    Route::put('/links', [LinksController::class, 'update']);

    Route::apiResources([
        '/links' => LinksController::class
    ]);
});

Route::get('/r/{slug}', [LinksController::class, 'redirect'])->name('slug.redirect');

Route::get('/links/{slug}/stats', [LinksController::class, 'statistics'])->name('slug.statistics');
