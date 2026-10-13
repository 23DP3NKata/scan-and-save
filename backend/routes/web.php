<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Middleware\EnsureActiveUser;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json(['application' => 'Scan & Save', 'status' => 'running']));

Route::prefix('api')->group(function (): void {
    Route::get('auth/csrf', [AuthController::class, 'csrf']);
    Route::post('auth/register', [AuthController::class, 'register'])->middleware('throttle:registration');
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:password-reset');
    Route::post('auth/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:password-reset');
    Route::middleware(['auth', EnsureActiveUser::class])->group(function (): void {
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('dashboard', DashboardController::class);
        Route::get('documents/{document}/file', [DocumentController::class, 'file'])->name('api.documents.file');
        Route::get('documents/{document}/download', [DocumentController::class, 'file'])->name('api.documents.download');
        Route::apiResource('documents', DocumentController::class);
        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('products', ProductController::class);
    });
});
