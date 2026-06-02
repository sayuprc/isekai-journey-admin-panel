<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Viewer\V1\Media\GetMediaController;
use App\Http\Controllers\Api\Viewer\V1\Media\ListMediaController;
use App\Http\Controllers\Api\Viewer\V1\SiteStats\GetSiteStatsController;
use App\Http\Controllers\Api\Viewer\V1\Song\GetSongController;
use App\Http\Controllers\Api\Viewer\V1\Song\ListSongController;
use App\Http\Middleware\Viewer\ViewerOpenApiValidator;
use Illuminate\Support\Facades\Route;
use Media\Route\ViewerMediaRouteMap;
use Song\Route\ViewerSongRouteMap;
use Support\Route\ViewerSiteStatsRouteMap;

Route::middleware(ViewerOpenApiValidator::class)->group(function () {
    Route::prefix('v1')->group(function () {
        Route::prefix('songs')->group(function () {
            Route::get('/', [ListSongController::class, 'handle'])->name(ViewerSongRouteMap::List);
            Route::get('/{songId}', [GetSongController::class, 'handle'])->name(ViewerSongRouteMap::Get);
        });
        Route::prefix('media')->group(function () {
            Route::get('/', [ListMediaController::class, 'handle'])->name(ViewerMediaRouteMap::List);
            Route::get('/{mediaId}', [GetMediaController::class, 'handle'])->name(ViewerMediaRouteMap::Get);
        });
        Route::get('/site-stats', [GetSiteStatsController::class, 'handle'])->name(ViewerSiteStatsRouteMap::Get);
    });
});
