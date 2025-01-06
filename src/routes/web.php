<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\JourneyLog\CreateJourneyLogController;
use App\Http\Controllers\JourneyLog\DeleteJourneyLogController;
use App\Http\Controllers\JourneyLog\EditJourneyLogController;
use App\Http\Controllers\JourneyLog\ListJourneyLogController;
use App\Http\Controllers\JourneyLogLinkType\CreateJourneyLogLinkTypeController;
use App\Http\Controllers\JourneyLogLinkType\DeleteJourneyLogLinkTypeController;
use App\Http\Controllers\JourneyLogLinkType\EditJourneyLogLinkTypeController;
use App\Http\Controllers\JourneyLogLinkType\ListJourneyLogLinkTypeController;
use App\Http\Controllers\Song\ListSongController;
use Illuminate\Support\Facades\Route;
use Shared\Route\RouteMap;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'showLoginForm'])
        ->name(RouteMap::SHOW_LOGIN_FORM);
    Route::post('/login', [LoginController::class, 'handle'])
        ->name(RouteMap::LOGIN);
});

Route::middleware('auth')->group(function (): void {
    Route::prefix('journey-logs')->group(function (): void {
        Route::get('/', [ListJourneyLogController::class, 'index'])
            ->name(RouteMap::LIST_JOURNEY_LOGS);

        Route::get('/create', [CreateJourneyLogController::class, 'index'])
            ->name(RouteMap::SHOW_CREATE_JOURNEY_LOG_FORM);
        Route::post('/create', [CreateJourneyLogController::class, 'handle'])
            ->name(RouteMap::CREATE_JOURNEY_LOG);

        Route::get('/{journeyLogId}', [EditJourneyLogController::class, 'index'])
            ->whereUuid('journeyLogId')
            ->name(RouteMap::SHOW_EDIT_JOURNEY_LOG_FORM);
        Route::post('/edit', [EditJourneyLogController::class, 'handle'])
            ->name(RouteMap::EDIT_JOURNEY_LOG);

        Route::delete('/', [DeleteJourneyLogController::class, 'handle'])
            ->name(RouteMap::DELETE_JOURNEY_LOG);
    });

    Route::prefix('journey-log-link-types')->group(function (): void {
        Route::get('/', [ListJourneyLogLinkTypeController::class, 'index'])
            ->name(RouteMap::LIST_JOURNEY_LOG_LINK_TYPE);

        Route::get('/create', [CreateJourneyLogLinkTypeController::class, 'index'])
            ->name(RouteMap::SHOW_CREATE_JOURNEY_LOG_LINK_TYPE_FORM);
        Route::post('/create', [CreateJourneyLogLinkTypeController::class, 'handle'])
            ->name(RouteMap::CREATE_JOURNEY_LOG_LINK_TYPE);

        Route::get('/{journeyLogLinkTypeId}', [EditJourneyLogLinkTypeController::class, 'index'])
            ->whereUuid('journeyLogLinkTypeId')
            ->name(RouteMap::SHOW_EDIT_JOURNEY_LOG_LINK_TYPE_FORM);
        Route::post('/edit', [EditJourneyLogLinkTypeController::class, 'handle'])
            ->name(RouteMap::EDIT_JOURNEY_LOG_LINK_TYPE);

        Route::delete('/', [DeleteJourneyLogLinkTypeController::class, 'handle'])
            ->name(RouteMap::DELETE_JOURNEY_LOG_LINK_TYPE);
    });

    Route::prefix('songs')->group(function (): void {
        Route::get('/', [ListSongController::class, 'index'])
            ->name(RouteMap::LIST_SONGS);
    });
});
