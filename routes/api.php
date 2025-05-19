<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\FavoriteCityController;
use App\Http\Controllers\API\WeatherController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('profile', [AuthController::class, 'profile']);
    });
    
    // Weather routes
    Route::middleware('permission:view weather')->group(function () {
        Route::post('weather/current', [WeatherController::class, 'getCurrentWeather']);
    });
    
    Route::middleware('permission:view history')->group(function () {
        Route::get('weather/history', [WeatherController::class, 'getSearchHistory']);
    });
    
    Route::middleware('permission:clear history')->group(function () {
        Route::delete('weather/history', [WeatherController::class, 'clearSearchHistory']);
    });
    
    // Favorite cities routes
    Route::middleware('permission:view favorites')->group(function () {
        Route::get('favorites', [FavoriteCityController::class, 'index']);
    });
    
    Route::middleware('permission:create favorites')->group(function () {
        Route::post('favorites', [FavoriteCityController::class, 'store']);
    });
    
    Route::middleware('permission:delete favorites')->group(function () {
        Route::delete('favorites/{id}', [FavoriteCityController::class, 'destroy']);
    });
});
