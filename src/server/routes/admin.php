<?php

declare(strict_types=1);

use AdminUser\Route\AdminUserRouteMap;
use App\Http\Controllers\Api\AdminUser\ListAdminUserController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Creator\CreateCreatorController;
use App\Http\Controllers\Api\Creator\DeleteCreatorController;
use App\Http\Controllers\Api\Creator\GetCreatorController;
use App\Http\Controllers\Api\Creator\ListCreatorController;
use App\Http\Controllers\Api\Creator\SearchCreatorController;
use App\Http\Controllers\Api\Creator\UpdateCreatorController;
use App\Http\Controllers\Api\Performer\CreatePerformerController;
use App\Http\Controllers\Api\Performer\DeletePerformerController;
use App\Http\Controllers\Api\Performer\GetPerformerController;
use App\Http\Controllers\Api\Performer\ListPerformerController;
use App\Http\Controllers\Api\Performer\SearchPerformerController;
use App\Http\Controllers\Api\Performer\UpdatePerformerController;
use App\Http\Controllers\Api\Song\CreateSongController;
use App\Http\Controllers\Api\Song\DeleteSongController;
use App\Http\Controllers\Api\Song\GetSongController;
use App\Http\Controllers\Api\Song\ListSongController;
use App\Http\Controllers\Api\Song\SearchSongController;
use App\Http\Controllers\Api\Song\UpdateSongController;
use App\Http\Controllers\Api\SongAttribute\ListSongAttributeController;
use App\Http\Controllers\Api\SongType\ListSongTypeController;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\OpenApiValidator;
use Auth\Route\AuthRouteMap;
use Creator\Route\CreatorRouteMap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Performer\Route\PerformerRouteMap;
use Song\Route\SongAttributeRouteMap;
use Song\Route\SongRouteMap;
use Song\Route\SongTypeRouteMap;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(OpenApiValidator::class)->group(function () {
    Route::prefix('admin')->group(function () {
        Route::prefix('v1')->group(function () {
            Route::prefix('auth')->group(function () {
                Route::post('/login', [LoginController::class, 'handle'])->name(AuthRouteMap::Login);
            });

            Route::middleware(Authenticate::class)->group(function () {
                Route::prefix('admin-users')->group(function () {
                    Route::get('/', [ListAdminUserController::class, 'handle'])->name(AdminUserRouteMap::List);
                });

                Route::prefix('creators')->group(function () {
                    Route::post('/', [CreateCreatorController::class, 'handle'])->name(CreatorRouteMap::Create);
                    Route::get('/', [ListCreatorController::class, 'handle'])->name(CreatorRouteMap::List);
                    Route::put('/{creatorId}', [UpdateCreatorController::class, 'handle'])->name(CreatorRouteMap::Update);
                    Route::delete('/{creatorId}', [DeleteCreatorController::class, 'handle'])->name(CreatorRouteMap::Delete);
                    Route::get('/search', [SearchCreatorController::class, 'handle'])->name(CreatorRouteMap::Search);
                    Route::get('/{creatorId}', [GetCreatorController::class, 'handle'])->name(CreatorRouteMap::Get);
                });

                Route::prefix('performers')->group(function () {
                    Route::post('/', [CreatePerformerController::class, 'handle'])->name(PerformerRouteMap::Create);
                    Route::get('/', [ListPerformerController::class, 'handle'])->name(PerformerRouteMap::List);
                    Route::put('/{performerId}', [UpdatePerformerController::class, 'handle'])->name(PerformerRouteMap::Update);
                    Route::delete('/{performerId}', [DeletePerformerController::class, 'handle'])->name(PerformerRouteMap::Delete);
                    Route::get('/search', [SearchPerformerController::class, 'handle'])->name(PerformerRouteMap::Search);
                    Route::get('/{performerId}', [GetPerformerController::class, 'handle'])->name(PerformerRouteMap::Get);
                });

                Route::prefix('songs')->group(function () {
                    Route::post('/', [CreateSongController::class, 'handle'])->name(SongRouteMap::Create);
                    Route::get('/', [ListSongController::class, 'handle'])->name(SongRouteMap::List);
                    Route::put('/{songId}', [UpdateSongController::class, 'handle'])->name(SongRouteMap::Update);
                    Route::delete('/{songId}', [DeleteSongController::class, 'handle'])->name(SongRouteMap::Delete);
                    Route::get('/search', [SearchSongController::class, 'handle'])->name(SongRouteMap::Search);
                    Route::get('/{songId}', [GetSongController::class, 'handle'])->name(SongRouteMap::Get);
                });

                Route::prefix('song-types')->group(function () {
                    Route::get('/', [ListSongTypeController::class, 'handle'])->name(SongTypeRouteMap::List);
                });

                Route::prefix('song-attributes')->group(function () {
                    Route::get('/', [ListSongAttributeController::class, 'handle'])->name(SongAttributeRouteMap::List);
                });
            });
        });
    });
});
