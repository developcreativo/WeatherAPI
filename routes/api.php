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
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('profile', [AuthController::class, 'profile']);
    
    // Weather routes
    Route::get('weather', [WeatherController::class, 'getCurrentWeather']);
    Route::get('weather/history', [WeatherController::class, 'getSearchHistory']);
    Route::delete('weather/history', [WeatherController::class, 'clearSearchHistory']);
    
    // Favorite cities routes
    Route::get('favorites', [FavoriteCityController::class, 'index']);
    Route::post('favorites', [FavoriteCityController::class, 'store']);
    Route::delete('favorites/{id}', [FavoriteCityController::class, 'destroy']);
});
