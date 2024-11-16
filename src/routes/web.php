<?php

declare(strict_types=1);

use App\Features\Auth\Adapter\Web\Controllers\LoginController;
use App\Features\JourneyLog\Adapter\Web\Controllers\CreateJourneyLogController;
use App\Features\JourneyLog\Adapter\Web\Controllers\DeleteJourneyLogController;
use App\Features\JourneyLog\Adapter\Web\Controllers\EditJourneyLogController;
use App\Features\JourneyLog\Adapter\Web\Controllers\ListJourneyLogController;
use App\Features\JourneyLogLinkType\Adapter\Web\Controllers\CreateJourneyLogLinkTypeController;
use App\Features\JourneyLogLinkType\Adapter\Web\Controllers\DeleteJourneyLogLinkTypeController;
use App\Features\JourneyLogLinkType\Adapter\Web\Controllers\EditJourneyLogLinkTypeController;
use App\Features\JourneyLogLinkType\Adapter\Web\Controllers\ListJourneyLogLinkTypeController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'handle'])->name('login.handle');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/journey-logs', [ListJourneyLogController::class, 'index'])->name('journey-logs.index');

    Route::get('/journey-logs/create', [CreateJourneyLogController::class, 'index'])->name('journey-logs.create.index');
    Route::post('/journey-logs/create', [CreateJourneyLogController::class, 'handle'])->name('journey-logs.create.handle');

    Route::get('/journey-logs/{journeyLogId}', [EditJourneyLogController::class, 'index'])
        ->whereUuid('journeyLogId')
        ->name('journey-logs.edit.index');
    Route::post('/journey-logs/edit', [EditJourneyLogController::class, 'handle'])
        ->name('journey-logs.edit.handle');

    Route::delete('/journey-logs', [DeleteJourneyLogController::class, 'handle'])
        ->name('journey-logs.delete.handle');

    Route::get('/journey-log-link-types', [ListJourneyLogLinkTypeController::class, 'index'])->name('journey-log-link-types.index');
    Route::get('/journey-log-link-types/create', [CreateJourneyLogLinkTypeController::class, 'index'])->name('journey-log-link-types.create.index');
    Route::post('/journey-log-link-types/create', [CreateJourneyLogLinkTypeController::class, 'handle'])->name('journey-log-link-types.create.handle');

    Route::get('/journey-log-link-types/{journeyLogLinkTypeId}', [EditJourneyLogLinkTypeController::class, 'index'])
        ->whereUuid('journeyLogLinkTypeId')
        ->name('journey-log-link-types.edit.index');
    Route::post('/journey-log-link-types', [EditJourneyLogLinkTypeController::class, 'handle'])
        ->name('journey-log-link-types.edit.handle');

    Route::delete('/journey-log-link-types', [DeleteJourneyLogLinkTypeController::class, 'handle'])
        ->name('journey-log-link-types.delete.handle');
});
