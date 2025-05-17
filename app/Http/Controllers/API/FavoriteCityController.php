<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\FavoriteCity;
use App\Services\WeatherService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteCityController extends Controller
{
    /**
     * The weather service instance.
     *
     * @var WeatherService
     */
    protected WeatherService $weatherService;

    /**
     * Create a new controller instance.
     *
     * @param WeatherService $weatherService
     */
    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    /**
     * Get all favorite cities for the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $favoriteCities = $request->user()->favoriteCities()->get();

        return response()->json([
            'message' => 'Favorite cities retrieved successfully',
            'data' => $favoriteCities,
        ]);
    }

    /**
     * Add a city to favorites.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'city' => 'required|string|max:255',
        ]);

        $city = $request->input('city');
        
        // Get weather data to ensure the city exists and get country information
        $weatherData = $this->weatherService->getCurrentWeather($city);
        
        if (!$weatherData) {
            return response()->json([
                'message' => 'Unable to find the specified city.',
            ], 404);
        }

        try {
            $favoriteCity = $request->user()->favoriteCities()->create([
                'city' => $weatherData['city'],
                'country' => $weatherData['country'],
            ]);

            return response()->json([
                'message' => 'City added to favorites successfully',
                'data' => $favoriteCity,
            ], 201);
        } catch (QueryException $e) {
            // Check if this is a duplicate entry error
            if ($e->getCode() === '23000') { // Integrity constraint violation
                return response()->json([
                    'message' => 'This city is already in your favorites',
                ], 422);
            }

            // For other database errors
            return response()->json([
                'message' => 'Failed to add city to favorites',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove a city from favorites.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $favoriteCity = $request->user()->favoriteCities()->findOrFail($id);
        $favoriteCity->delete();

        return response()->json([
            'message' => 'City removed from favorites successfully',
        ]);
    }
}
