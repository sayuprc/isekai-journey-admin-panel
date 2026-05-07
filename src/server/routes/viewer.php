<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Viewer\V1\Song\ListSongController;
use App\Http\Middleware\Viewer\ViewerOpenApiValidator;
use Illuminate\Support\Facades\Route;
use Song\Route\ViewerSongRouteMap;

Route::middleware(ViewerOpenApiValidator::class)->group(function () {
    Route::prefix('v1')->group(function () {
        Route::prefix('songs')->group(function () {
            Route::get('/', [ListSongController::class, 'handle'])->name(ViewerSongRouteMap::List);
        });
    });
});
