<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\ApplicationController;

// ── Auth routes (public) ──────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);

    // Protected auth routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me',      [AuthController::class, 'me']);
    });
});

// ── Public job routes ─────────────────────────────────────────
Route::prefix('jobs')->group(function () {
    Route::get('/',           [JobController::class, 'index']);
    Route::get('/featured',   [JobController::class, 'featured']);
    Route::get('/categories', [JobController::class, 'categories']);
    Route::get('/{id}',       [JobController::class, 'show']);
});

// ── Seeker routes (must be logged in) ────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/applications', [ApplicationController::class, 'store']);
});

// ── Employer routes ───────────────────────────────────────────
Route::middleware(['auth:sanctum'])->prefix('employer')->group(function () {
    Route::post('/jobs',        [JobController::class, 'store']);
    Route::put('/jobs/{id}',    [JobController::class, 'update']);
    Route::delete('/jobs/{id}', [JobController::class, 'destroy']);
});

// ── Admin routes ──────────────────────────────────────────────
Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    Route::get('/applications',       [ApplicationController::class, 'index']);
    Route::get('/applications/{id}',  [ApplicationController::class, 'show']);
    Route::delete('/applications/{id}', [ApplicationController::class, 'destroy']);
});
