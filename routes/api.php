<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SavedJobController;

// ── Auth ──────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me',      [AuthController::class, 'me']);
    });
});

// ── Public jobs ───────────────────────────────────────────────
Route::prefix('jobs')->group(function () {
    Route::get('/',           [JobController::class, 'index']);
    Route::get('/featured',   [JobController::class, 'featured']);
    Route::get('/categories', [JobController::class, 'categories']);
    Route::get('/{id}',       [JobController::class, 'show']);
});

// ── Public companies ──────────────────────────────────────────
Route::get('/companies',      [ProfileController::class, 'companies']);
Route::get('/companies/{id}', [ProfileController::class, 'companyShow']);

// ── Authenticated ─────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    // Applications
    Route::post('/applications',             [ApplicationController::class, 'store']);
    Route::get('/seeker/applications',       [ApplicationController::class, 'myApplications']);
    Route::get('/seeker/applications/check', [ApplicationController::class, 'checkApplied']);

    // Saved jobs
    Route::get('/saved-jobs',        [SavedJobController::class, 'index']);
    Route::post('/saved-jobs/toggle',[SavedJobController::class, 'toggle']);
    Route::get('/saved-jobs/check',  [SavedJobController::class, 'check']);
    Route::get('/saved-jobs/ids',    [SavedJobController::class, 'ids']);

    // Employer
    Route::prefix('employer')->group(function () {
        Route::post('/jobs',        [JobController::class, 'store']);
        Route::put('/jobs/{id}',    [JobController::class, 'update']);
        Route::delete('/jobs/{id}', [JobController::class, 'destroy']);
    });

    // Admin
    Route::prefix('admin')->group(function () {
        Route::get('/applications',               [ApplicationController::class, 'index']);
        Route::get('/applications/{id}',          [ApplicationController::class, 'show']);
        Route::patch('/applications/{id}/status', [ApplicationController::class, 'updateStatus']);
        Route::delete('/applications/{id}',       [ApplicationController::class, 'destroy']);
    });
});
