<?php

declare(strict_types=1);

use AdminUser\Route\AdminUserRouteMap;
use App\Http\Controllers\Api\Admin\V1\AdminUser\ListAdminUserController;
use App\Http\Controllers\Api\Admin\V1\AuditLog\GetAuditLogController;
use App\Http\Controllers\Api\Admin\V1\AuditLog\SearchAuditLogController;
use App\Http\Controllers\Api\Admin\V1\Auth\LoginController;
use App\Http\Controllers\Api\Admin\V1\Auth\RefreshController;
use App\Http\Controllers\Api\Admin\V1\Media\CreateMediaController;
use App\Http\Controllers\Api\Admin\V1\Media\DeleteMediaController;
use App\Http\Controllers\Api\Admin\V1\Media\GetMediaController;
use App\Http\Controllers\Api\Admin\V1\Media\SearchMediaController;
use App\Http\Controllers\Api\Admin\V1\Media\UpdateMediaController;
use App\Http\Controllers\Api\Admin\V1\Person\CreatePersonController;
use App\Http\Controllers\Api\Admin\V1\Person\DeletePersonController;
use App\Http\Controllers\Api\Admin\V1\Person\GetPersonController;
use App\Http\Controllers\Api\Admin\V1\Person\ListPersonController;
use App\Http\Controllers\Api\Admin\V1\Person\SearchPersonController;
use App\Http\Controllers\Api\Admin\V1\Person\UpdatePersonController;
use App\Http\Controllers\Api\Admin\V1\Release\CreateReleaseController;
use App\Http\Controllers\Api\Admin\V1\Release\DeleteReleaseController;
use App\Http\Controllers\Api\Admin\V1\Release\GetReleaseController;
use App\Http\Controllers\Api\Admin\V1\Release\SearchReleaseController;
use App\Http\Controllers\Api\Admin\V1\Release\UpdateReleaseController;
use App\Http\Controllers\Api\Admin\V1\Song\CreateSongController;
use App\Http\Controllers\Api\Admin\V1\Song\DeleteSongController;
use App\Http\Controllers\Api\Admin\V1\Song\GetSongController;
use App\Http\Controllers\Api\Admin\V1\Song\SearchSongController;
use App\Http\Controllers\Api\Admin\V1\Song\UpdateSongController;
use App\Http\Controllers\Api\Admin\V1\SongTag\CreateSongTagController;
use App\Http\Controllers\Api\Admin\V1\SongTag\DeleteSongTagController;
use App\Http\Controllers\Api\Admin\V1\SongTag\GetSongTagController;
use App\Http\Controllers\Api\Admin\V1\SongTag\ListSongTagController;
use App\Http\Controllers\Api\Admin\V1\SongTag\SearchSongTagController;
use App\Http\Controllers\Api\Admin\V1\SongTag\UpdateSongTagController;
use App\Http\Controllers\Api\Admin\V1\SongType\ListSongTypeController;
use App\Http\Middleware\Admin\AdminOpenApiValidator;
use App\Http\Middleware\Admin\Authenticate;
use Auth\Route\AuthRouteMap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Media\Route\MediaRouteMap;
use Person\Route\PersonRouteMap;
use Release\Route\ReleaseRouteMap;
use Song\Route\SongRouteMap;
use Song\Route\SongTypeRouteMap;
use Song\Route\Tag\SongTagRouteMap;
use Support\Route\AuditLogRouteMap;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(AdminOpenApiValidator::class)->group(function () {
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

                Route::prefix('media')->group(function () {
                    Route::post('/', [CreateMediaController::class, 'handle'])->name(MediaRouteMap::Create);
                    Route::delete('/{mediaId}', [DeleteMediaController::class, 'handle'])->name(MediaRouteMap::Delete);
                    Route::put('/{mediaId}', [UpdateMediaController::class, 'handle'])->name(MediaRouteMap::Update);
                    Route::get('/search', [SearchMediaController::class, 'handle'])->name(MediaRouteMap::Search);
                    Route::get('/{mediaId}', [GetMediaController::class, 'handle'])->name(MediaRouteMap::Get);
                });

                Route::prefix('songs')->group(function () {
                    Route::post('/', [CreateSongController::class, 'handle'])->name(SongRouteMap::Create);
                    Route::put('/{songId}', [UpdateSongController::class, 'handle'])->name(SongRouteMap::Update);
                    Route::delete('/{songId}', [DeleteSongController::class, 'handle'])->name(SongRouteMap::Delete);
                    Route::get('/search', [SearchSongController::class, 'handle'])->name(SongRouteMap::Search);
                    Route::get('/{songId}', [GetSongController::class, 'handle'])->name(SongRouteMap::Get);
                });

                Route::prefix('releases')->group(function () {
                    Route::post('/', [CreateReleaseController::class, 'handle'])->name(ReleaseRouteMap::Create);
                    Route::put('/{releaseId}', [UpdateReleaseController::class, 'handle'])->name(ReleaseRouteMap::Update);
                    Route::delete('/{releaseId}', [DeleteReleaseController::class, 'handle'])->name(ReleaseRouteMap::Delete);
                    Route::get('/search', [SearchReleaseController::class, 'handle'])->name(ReleaseRouteMap::Search);
                    Route::get('/{releaseId}', [GetReleaseController::class, 'handle'])->name(ReleaseRouteMap::Get);
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
