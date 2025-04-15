<?php

declare(strict_types=1);

use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\Creator\CreateCreatorController;
use App\Http\Controllers\Web\JourneyLog\CreateJourneyLogController;
use App\Http\Controllers\Web\JourneyLog\DeleteJourneyLogController;
use App\Http\Controllers\Web\JourneyLog\EditJourneyLogController;
use App\Http\Controllers\Web\JourneyLog\ListJourneyLogController;
use App\Http\Controllers\Web\JourneyLogLinkType\CreateJourneyLogLinkTypeController;
use App\Http\Controllers\Web\JourneyLogLinkType\DeleteJourneyLogLinkTypeController;
use App\Http\Controllers\Web\JourneyLogLinkType\EditJourneyLogLinkTypeController;
use App\Http\Controllers\Web\JourneyLogLinkType\ListJourneyLogLinkTypeController;
use App\Http\Controllers\Web\Song\ListSongController;
use Illuminate\Support\Facades\Route;
use Support\Route\RouteMap;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'showLoginForm'])
        ->name(RouteMap::ShowLoginForm);
    Route::post('/login', [LoginController::class, 'handle'])
        ->name(RouteMap::Login);
});

Route::middleware('auth')->group(function (): void {
    Route::prefix('journey-logs')->group(function (): void {
        Route::get('/', [ListJourneyLogController::class, 'index'])
            ->name(RouteMap::ListJourneyLogs);

        Route::get('/create', [CreateJourneyLogController::class, 'index'])
            ->name(RouteMap::ShowCreateJourneyLogForm);
        Route::post('/create', [CreateJourneyLogController::class, 'handle'])
            ->name(RouteMap::CreateJourneyLog);

        Route::get('/{journeyLogId}', [EditJourneyLogController::class, 'index'])
            ->whereUuid('journeyLogId')
            ->name(RouteMap::ShowEditJourneyLogForm);
        Route::post('/edit', [EditJourneyLogController::class, 'handle'])
            ->name(RouteMap::EditJourneyLog);

        Route::delete('/', [DeleteJourneyLogController::class, 'handle'])
            ->name(RouteMap::DeleteJourneyLog);
    });

    Route::prefix('journey-log-link-types')->group(function (): void {
        Route::get('/', [ListJourneyLogLinkTypeController::class, 'index'])
            ->name(RouteMap::ListJourneyLogLinkType);

        Route::get('/create', [CreateJourneyLogLinkTypeController::class, 'index'])
            ->name(RouteMap::ShowCreateJourneyLogLinkTypeForm);
        Route::post('/create', [CreateJourneyLogLinkTypeController::class, 'handle'])
            ->name(RouteMap::CreateJourneyLogLinkType);

        Route::get('/{journeyLogLinkTypeId}', [EditJourneyLogLinkTypeController::class, 'index'])
            ->whereUuid('journeyLogLinkTypeId')
            ->name(RouteMap::ShowEditJourneyLogLinkTypeForm);
        Route::post('/edit', [EditJourneyLogLinkTypeController::class, 'handle'])
            ->name(RouteMap::EditJourneyLogLinkType);

        Route::delete('/', [DeleteJourneyLogLinkTypeController::class, 'handle'])
            ->name(RouteMap::DeleteJourneyLogLinkType);
    });

    Route::prefix('songs')->group(function (): void {
        Route::get('/', [ListSongController::class, 'index'])
            ->name(RouteMap::ListSongs);
    });

    Route::prefix('creators')->group(function (): void {
        Route::get('/create', [CreateCreatorController::class, 'index'])
            ->name(RouteMap::ShowCreateCreatorForm);
        Route::post('/create', [CreateCreatorController::class, 'handle'])
            ->name(RouteMap::CreateCreator);
    });
});
