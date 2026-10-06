<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json(['application' => 'Scan & Save', 'status' => 'running']));

Route::prefix('api')->group(function (): void {
    Route::get('auth/csrf', [AuthController::class, 'csrf']);
    Route::post('auth/register', [AuthController::class, 'register'])->middleware('throttle:registration');
});
