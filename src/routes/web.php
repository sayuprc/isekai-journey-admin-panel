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
});
