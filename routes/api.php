<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ProfileController;

// ── Auth (public) ─────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);

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

// ── Public company routes ─────────────────────────────────────
Route::get('/companies',      [ProfileController::class, 'companies']);
Route::get('/companies/{id}', [ProfileController::class, 'companyShow']);

// ── Authenticated routes ──────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Profile (seeker or employer based on role)
    Route::get('/profile',  [ProfileController::class, 'show']);
    Route::put('/profile',  [ProfileController::class, 'update']);

    // Applications (seeker)
    Route::post('/applications', [ApplicationController::class, 'store']);

    // Employer job management
    Route::prefix('employer')->group(function () {
        Route::post('/jobs',        [JobController::class, 'store']);
        Route::put('/jobs/{id}',    [JobController::class, 'update']);
        Route::delete('/jobs/{id}', [JobController::class, 'destroy']);
    });

    // Admin
    Route::prefix('admin')->group(function () {
        Route::get('/applications',         [ApplicationController::class, 'index']);
        Route::get('/applications/{id}',    [ApplicationController::class, 'show']);
        Route::delete('/applications/{id}', [ApplicationController::class, 'destroy']);
    });
});
