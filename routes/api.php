<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;


Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('vehicles', VehicleController::class);
});