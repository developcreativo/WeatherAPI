<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\WeatherSearch;
use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WeatherController extends Controller
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
     * Get current weather for a city.
     * 
     * @OA\Post(
     *     path="/api/weather/current",
     *     summary="Get current weather for a city",
     *     tags={"Weather"},
     *     security={"sanctum": {}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"city"},
     *             @OA\Property(property="city", type="string", example="London")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Weather data retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="City not found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getCurrentWeather(Request $request): JsonResponse
    {
        $request->validate([
            'city' => 'required|string|max:255',
        ]);

        $city = $request->input('city');
        $weatherData = $this->weatherService->getCurrentWeather($city);

        if (!$weatherData) {
            return response()->json([
                'message' => __('weather.weather_data_failure'),
            ], 404);
        }

        // Save the search to history
        try {
            $request->user()->weatherSearches()->create($weatherData);
        } catch (\Exception $e) {
            Log::error('Failed to save weather search history', [
                'error' => $e->getMessage(),
                'city' => $city,
            ]);
        }

        return response()->json([
            'message' => __('weather.weather_data_success'),
            'data' => $weatherData,
        ]);
    }

    /**
     * Get weather search history for the authenticated user.
     * 
     * @OA\Get(
     *     path="/api/weather/history",
     *     summary="Get weather search history",
     *     tags={"Weather"},
     *     security={"sanctum": {}},
     *     @OA\Response(
     *         response=200,
     *         description="Search history retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getSearchHistory(Request $request): JsonResponse
    {
        if (!$request->user()->can('view history')) {
            return response()->json(['message' => __('weather.unauthorized')], 403);
        }
        
        $history = $request->user()->weatherSearches()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'message' => __('weather.search_history_success'),
            'data' => $history,
        ]);
    }

    /**
     * Clear weather search history for the authenticated user.
     * 
     * @OA\Delete(
     *     path="/api/weather/history",
     *     summary="Clear weather search history",
     *     tags={"Weather"},
     *     security={"sanctum": {}},
     *     @OA\Response(
     *         response=200,
     *         description="Search history cleared successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function clearSearchHistory(Request $request): JsonResponse
    {
        if (!$request->user()->can('clear history')) {
            return response()->json(['message' => __('weather.unauthorized')], 403);
        }
        
        $request->user()->weatherSearches()->delete();

        return response()->json([
            'message' => __('weather.search_history_clear_success'),
        ]);
    }
}
