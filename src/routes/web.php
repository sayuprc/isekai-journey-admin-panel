<?php

declare(strict_types=1);

use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\Creator\CreateCreatorController;
use App\Http\Controllers\Web\Creator\DeleteCreatorController;
use App\Http\Controllers\Web\Creator\EditCreatorController;
use App\Http\Controllers\Web\Creator\ListCreatorController;
use App\Http\Controllers\Web\JourneyLog\CreateJourneyLogController;
use App\Http\Controllers\Web\JourneyLog\DeleteJourneyLogController;
use App\Http\Controllers\Web\JourneyLog\EditJourneyLogController;
use App\Http\Controllers\Web\JourneyLog\ListJourneyLogController;
use App\Http\Controllers\Web\JourneyLogLinkType\CreateJourneyLogLinkTypeController;
use App\Http\Controllers\Web\JourneyLogLinkType\DeleteJourneyLogLinkTypeController;
use App\Http\Controllers\Web\JourneyLogLinkType\EditJourneyLogLinkTypeController;
use App\Http\Controllers\Web\JourneyLogLinkType\ListJourneyLogLinkTypeController;
use App\Http\Controllers\Web\Song\CreateSongController;
use App\Http\Controllers\Web\Song\ListSongController;
use App\Http\Controllers\Web\SongType\CreateSongTypeController;
use App\Http\Controllers\Web\SongType\DeleteSongTypeController;
use App\Http\Controllers\Web\SongType\EditSongTypeController;
use App\Http\Controllers\Web\SongType\ListSongTypeController;
use Auth\Route\AuthRouteMap;
use Creator\Route\CreatorRouteMap;
use Illuminate\Support\Facades\Route;
use JourneyLog\Route\JourneyLogRouteMap;
use JourneyLogLinkType\Route\JourneyLogLinkTypeRouteMap;
use Song\Route\SongRouteMap;
use SongType\Route\SongTypeRouteMap;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'showLoginForm'])
        ->name(AuthRouteMap::ShowLoginForm);
    Route::post('/login', [LoginController::class, 'handle'])
        ->name(AuthRouteMap::Login);
});

Route::middleware('auth')->group(function (): void {
    Route::prefix('journey-logs')->group(function (): void {
        Route::get('/', [ListJourneyLogController::class, 'index'])
            ->name(JourneyLogRouteMap::List);

        Route::get('/create', [CreateJourneyLogController::class, 'index'])
            ->name(JourneyLogRouteMap::ShowCreateForm);
        Route::post('/create', [CreateJourneyLogController::class, 'handle'])
            ->name(JourneyLogRouteMap::Create);

        Route::get('/{journeyLogId}', [EditJourneyLogController::class, 'index'])
            ->whereUuid('journeyLogId')
            ->name(JourneyLogRouteMap::ShowEditForm);
        Route::post('/edit', [EditJourneyLogController::class, 'handle'])
            ->name(JourneyLogRouteMap::Edit);

        Route::delete('/', [DeleteJourneyLogController::class, 'handle'])
            ->name(JourneyLogRouteMap::Delete);
    });

    Route::prefix('journey-log-link-types')->group(function (): void {
        Route::get('/', [ListJourneyLogLinkTypeController::class, 'index'])
            ->name(JourneyLogLinkTypeRouteMap::List);

        Route::get('/create', [CreateJourneyLogLinkTypeController::class, 'index'])
            ->name(JourneyLogLinkTypeRouteMap::ShowCreateForm);
        Route::post('/create', [CreateJourneyLogLinkTypeController::class, 'handle'])
            ->name(JourneyLogLinkTypeRouteMap::Create);

        Route::get('/{journeyLogLinkTypeId}', [EditJourneyLogLinkTypeController::class, 'index'])
            ->whereUuid('journeyLogLinkTypeId')
            ->name(JourneyLogLinkTypeRouteMap::ShowEditForm);
        Route::post('/edit', [EditJourneyLogLinkTypeController::class, 'handle'])
            ->name(JourneyLogLinkTypeRouteMap::Edit);

        Route::delete('/', [DeleteJourneyLogLinkTypeController::class, 'handle'])
            ->name(JourneyLogLinkTypeRouteMap::Delete);
    });

    Route::prefix('songs')->group(function (): void {
        Route::get('/', [ListSongController::class, 'index'])
            ->name(SongRouteMap::List);

        Route::get('/create', [CreateSongController::class, 'index'])
            ->name(SongRouteMap::ShowCreateForm);
        Route::post('/create', [CreateSongController::class, 'handle'])
            ->name(SongRouteMap::Create);
    });

    Route::prefix('song-types')->group(function (): void {
        Route::get('/', [ListSongTypeController::class, 'index'])
            ->name(SongTypeRouteMap::List);

        Route::get('/create', [CreateSongTypeController::class, 'index'])
            ->name(SongTypeRouteMap::ShowCreateForm);
        Route::post('/create', [CreateSongTypeController::class, 'handle'])
            ->name(SongTypeRouteMap::Create);

        Route::get('/{songTypeId}', [EditSongTypeController::class, 'index'])
            ->whereUuid('songTypeId')
            ->name(SongTypeRouteMap::ShowEditForm);
        Route::post('/edit', [EditSongTypeController::class, 'handle'])
            ->name(SongTypeRouteMap::Edit);

        Route::delete('/', [DeleteSongTypeController::class, 'handle'])
            ->name(SongTypeRouteMap::Delete);
    });

    Route::prefix('creators')->group(function (): void {
        Route::get('/', [ListCreatorController::class, 'index'])
            ->name(CreatorRouteMap::List);

        Route::get('/create', [CreateCreatorController::class, 'index'])
            ->name(CreatorRouteMap::ShowCreateForm);
        Route::post('/create', [CreateCreatorController::class, 'handle'])
            ->name(CreatorRouteMap::Create);

        Route::get('/{creatorId}', [EditCreatorController::class, 'index'])
            ->whereUuid('creatorId')
            ->name(CreatorRouteMap::ShowEditForm);
        Route::post('/edit', [EditCreatorController::class, 'handle'])
            ->name(CreatorRouteMap::Edit);

        Route::delete('/', [DeleteCreatorController::class, 'handle'])
            ->name(CreatorRouteMap::Delete);
    });
});
