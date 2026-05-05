<?php

declare(strict_types=1);

use AdminUser\Route\AdminUserRouteMap;
use App\Http\Controllers\Api\AdminUser\ListAdminUserController;
use App\Http\Controllers\Api\AuditLog\GetAuditLogController;
use App\Http\Controllers\Api\AuditLog\SearchAuditLogController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RefreshController;
use App\Http\Controllers\Api\Person\CreatePersonController;
use App\Http\Controllers\Api\Person\DeletePersonController;
use App\Http\Controllers\Api\Person\GetPersonController;
use App\Http\Controllers\Api\Person\ListPersonController;
use App\Http\Controllers\Api\Person\SearchPersonController;
use App\Http\Controllers\Api\Person\UpdatePersonController;
use App\Http\Controllers\Api\Song\CreateSongController;
use App\Http\Controllers\Api\Song\DeleteSongController;
use App\Http\Controllers\Api\Song\GetSongController;
use App\Http\Controllers\Api\Song\SearchSongController;
use App\Http\Controllers\Api\Song\UpdateSongController;
use App\Http\Controllers\Api\SongTag\CreateSongTagController;
use App\Http\Controllers\Api\SongTag\DeleteSongTagController;
use App\Http\Controllers\Api\SongTag\GetSongTagController;
use App\Http\Controllers\Api\SongTag\ListSongTagController;
use App\Http\Controllers\Api\SongTag\SearchSongTagController;
use App\Http\Controllers\Api\SongTag\UpdateSongTagController;
use App\Http\Controllers\Api\SongType\ListSongTypeController;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\OpenApiValidator;
use Auth\Route\AuthRouteMap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Person\Route\PersonRouteMap;
use Song\Route\SongRouteMap;
use Song\Route\SongTypeRouteMap;
use Song\Route\Tag\SongTagRouteMap;
use Support\Route\AuditLogRouteMap;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(OpenApiValidator::class)->group(function () {
    Route::prefix('admin')->group(function () {
        Route::prefix('v1')->group(function () {
            Route::prefix('auth')->group(function () {
                Route::post('/login', [LoginController::class, 'handle'])->name(AuthRouteMap::Login);
                Route::post('/refresh', [RefreshController::class, 'handle'])->name(AuthRouteMap::Refresh);
            });

            Route::middleware(Authenticate::class)->group(function () {
                Route::prefix('admin-users')->group(function () {
                    Route::get('/', [ListAdminUserController::class, 'handle'])->name(AdminUserRouteMap::List);
                });

                Route::prefix('persons')->group(function () {
                    Route::post('/', [CreatePersonController::class, 'handle'])->name(PersonRouteMap::Create);
                    Route::get('/', [ListPersonController::class, 'handle'])->name(PersonRouteMap::List);
                    Route::put('/{personId}', [UpdatePersonController::class, 'handle'])->name(PersonRouteMap::Update);
                    Route::delete('/{personId}', [DeletePersonController::class, 'handle'])->name(PersonRouteMap::Delete);
                    Route::get('/search', [SearchPersonController::class, 'handle'])->name(PersonRouteMap::Search);
                    Route::get('/{personId}', [GetPersonController::class, 'handle'])->name(PersonRouteMap::Get);
                });

                Route::prefix('songs')->group(function () {
                    Route::post('/', [CreateSongController::class, 'handle'])->name(SongRouteMap::Create);
                    Route::put('/{songId}', [UpdateSongController::class, 'handle'])->name(SongRouteMap::Update);
                    Route::delete('/{songId}', [DeleteSongController::class, 'handle'])->name(SongRouteMap::Delete);
                    Route::get('/search', [SearchSongController::class, 'handle'])->name(SongRouteMap::Search);
                    Route::get('/{songId}', [GetSongController::class, 'handle'])->name(SongRouteMap::Get);
                });

                Route::prefix('song-types')->group(function () {
                    Route::get('/', [ListSongTypeController::class, 'handle'])->name(SongTypeRouteMap::List);
                });

                Route::prefix('audit-logs')->group(function () {
                    Route::get('/search', [SearchAuditLogController::class, 'handle'])->name(AuditLogRouteMap::Search);
                    Route::get('/{auditLogId}', [GetAuditLogController::class, 'handle'])->name(AuditLogRouteMap::Get);
                });

                Route::prefix('song-tags')->group(function () {
                    Route::post('/', [CreateSongTagController::class, 'handle'])->name(SongTagRouteMap::Create);
                    Route::get('/', [ListSongTagController::class, 'handle'])->name(SongTagRouteMap::List);
                    Route::put('/{songTagId}', [UpdateSongTagController::class, 'handle'])->name(SongTagRouteMap::Update);
                    Route::delete('/{songTagId}', [DeleteSongTagController::class, 'handle'])->name(SongTagRouteMap::Delete);
                    Route::get('/search', [SearchSongTagController::class, 'handle'])->name(SongTagRouteMap::Search);
                    Route::get('/{songTagId}', [GetSongTagController::class, 'handle'])->name(SongTagRouteMap::Get);
                });
            });
        });
    });
});
