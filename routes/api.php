<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobController;
use App\Http\Controllers\ApplicationController;

/*
|--------------------------------------------------------------------------
| QuickHire API Routes
|--------------------------------------------------------------------------
*/

// Public job routes
Route::prefix('jobs')->group(function () {
    Route::get('/', [JobController::class, 'index']);
    Route::get('/featured', [JobController::class, 'featured']);
    Route::get('/categories', [JobController::class, 'categories']);
    Route::get('/{id}', [JobController::class, 'show']);
});

// Public application submission
Route::post('/applications', [ApplicationController::class, 'store']);

// Admin routes (protect with middleware in production)
Route::prefix('admin')->group(function () {
    Route::post('/jobs', [JobController::class, 'store']);
    Route::put('/jobs/{id}', [JobController::class, 'update']);
    Route::delete('/jobs/{id}', [JobController::class, 'destroy']);

    Route::get('/applications', [ApplicationController::class, 'index']);
    Route::get('/applications/{id}', [ApplicationController::class, 'show']);
    Route::delete('/applications/{id}', [ApplicationController::class, 'destroy']);
});
