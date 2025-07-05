<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Creator\CreateCreatorController;
use App\Http\Controllers\Api\Creator\GetCreatorController;
use App\Http\Controllers\Api\Creator\ListCreatorController;
use App\Http\Controllers\Api\SongType\CreateSongTypeController;
use App\Http\Controllers\Api\SongType\DeleteSongTypeController;
use App\Http\Controllers\Api\SongType\GetSongTypeController;
use App\Http\Controllers\Api\SongType\ListSongTypeController;
use App\Http\Controllers\Api\SongType\UpdateSongTypeController;
use Creator\Route\CreatorRouteMap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use SongType\Route\SongTypeRouteMap;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('api')->group(function () {
    Route::prefix('creators')->group(function () {
        Route::post('/', [CreateCreatorController::class, 'handle'])->name(CreatorRouteMap::Create);
        Route::get('/', [ListCreatorController::class, 'handle'])->name(CreatorRouteMap::List);
        Route::get('/{creatorId}', [GetCreatorController::class, 'handle'])->name(CreatorRouteMap::Get);
    });

    Route::prefix('song-types')->group(function () {
        // TODO ログインが必要
        Route::post('/', [CreateSongTypeController::class, 'handle'])->name(SongTypeRouteMap::Create);
        Route::get('/', [ListSongTypeController::class, 'handle'])->name(SongTypeRouteMap::List);
        Route::put('/{songTypeId}', [UpdateSongTypeController::class, 'handle'])->name(SongTypeRouteMap::Update);
        Route::delete('/{songTypeId}', [DeleteSongTypeController::class, 'handle'])->name(SongTypeRouteMap::Delete);
        Route::get('/{songTypeId}', [GetSongTypeController::class, 'handle'])->name(SongTypeRouteMap::Get);
    });
});
