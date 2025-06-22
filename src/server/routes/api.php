<?php

declare(strict_types=1);

use App\Http\Controllers\Api\SongType\ListSongTypeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use SongType\Route\SongTypeRouteMap;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('api')->group(function () {
    Route::prefix('song-types')->group(function () {
        Route::get('/', [ListSongTypeController::class, 'handle'])->name(SongTypeRouteMap::List);
    });
});
