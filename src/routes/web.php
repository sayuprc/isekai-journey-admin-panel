<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\JourneyLog\CreateJourneyLogController;
use App\Http\Controllers\JourneyLog\EditJourneyLogController;
use App\Http\Controllers\JourneyLog\ListJourneyLogsController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'handle'])->name('login.handle');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/journey-logs', [ListJourneyLogsController::class, 'index'])->name('journey-logs.index');

    Route::get('/journey-logs/create', [CreateJourneyLogController::class, 'index'])->name('journey-logs.create.index');
    Route::post('/journey-logs/create', [CreateJourneyLogController::class, 'handle'])->name('journey-logs.create.handle');

    Route::get('/journey-logs/{journeyLogId}', [EditJourneyLogController::class, 'index'])
        ->whereUuid('journeyLogId')
        ->name('journey-logs.edit.index');
    Route::post('/journey-logs/edit', [EditJourneyLogController::class, 'handle'])
        ->name('journey-logs.edit.handle');
});
