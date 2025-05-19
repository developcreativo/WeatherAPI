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
     * @OA\Get(
     *     path="/api/favorites",
     *     summary="Get user's favorite cities",
     *     tags={"Favorites"},
     *     security={"sanctum": {}},
     *     @OA\Response(
     *         response=200,
     *         description="Favorite cities retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $favoriteCities = $request->user()->favoriteCities()->get();

        return response()->json([
            'message' => __('weather.favorite_cities_success'),
            'data' => $favoriteCities,
        ]);
    }

    /**
     * Add a city to favorites.
     * 
     * @OA\Post(
     *     path="/api/favorites",
     *     summary="Add a city to favorites",
     *     tags={"Favorites"},
     *     security={"sanctum": {}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"city"},
     *             @OA\Property(property="city", type="string", example="London")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="City added to favorites successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="City not found"),
     *     @OA\Response(response=422, description="City already in favorites"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
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
                'message' => __('weather.weather_data_failure'),
            ], 404);
        }

        try {
            $favoriteCity = $request->user()->favoriteCities()->create([
                'city' => $weatherData['city'],
                'country' => $weatherData['country'],
            ]);

            return response()->json([
                'message' => __('weather.city_favorite_success'),
                'data' => $favoriteCity,
            ], 201);
        } catch (QueryException $e) {
            // Check if this is a duplicate entry error
            if ($e->getCode() === '23000') { // Integrity constraint violation
                return response()->json([
                    'message' => __('weather.city_favorite_exists'),
                ], 422);
            }

            // For other database errors
            return response()->json([
                'message' => __('weather.city_favorite_failure'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove a city from favorites.
     * 
     * @OA\Delete(
     *     path="/api/favorites/{id}",
     *     summary="Remove a city from favorites",
     *     tags={"Favorites"},
     *     security={"sanctum": {}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="City removed from favorites successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=404, description="City not found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
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
            'message' => __('weather.city_favorite_remove_success'),
        ]);
    }
}
