<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Creator\CreateCreatorController;
use App\Http\Controllers\Api\Creator\DeleteCreatorController;
use App\Http\Controllers\Api\Creator\GetCreatorController;
use App\Http\Controllers\Api\Creator\ListCreatorController;
use App\Http\Controllers\Api\Creator\UpdateCreatorController;
use App\Http\Controllers\Api\Performer\CreatePerformerController;
use App\Http\Controllers\Api\SongType\CreateSongTypeController;
use App\Http\Controllers\Api\SongType\DeleteSongTypeController;
use App\Http\Controllers\Api\SongType\GetSongTypeController;
use App\Http\Controllers\Api\SongType\ListSongTypeController;
use App\Http\Controllers\Api\SongType\UpdateSongTypeController;
use App\Http\Controllers\Api\User\LoginController;
use Creator\Route\CreatorRouteMap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Performer\Route\PerformerRouteMap;
use SongType\Route\SongTypeRouteMap;
use User\Route\UserRouteMap;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('api')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/login', [LoginController::class, 'handle'])->name(UserRouteMap::Login);
    });

    Route::prefix('creators')->group(function () {
        Route::post('/', [CreateCreatorController::class, 'handle'])->name(CreatorRouteMap::Create);
        Route::get('/', [ListCreatorController::class, 'handle'])->name(CreatorRouteMap::List);
        Route::put('/{creatorId}', [UpdateCreatorController::class, 'handle'])->name(CreatorRouteMap::Update);
        Route::delete('/{creatorId}', [DeleteCreatorController::class, 'handle'])->name(CreatorRouteMap::Delete);
        Route::get('/{creatorId}', [GetCreatorController::class, 'handle'])->name(CreatorRouteMap::Get);
    });

    Route::prefix('performers')->group(function () {
        Route::post('/', [CreatePerformerController::class, 'handle'])->name(PerformerRouteMap::Create);
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
