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
                'message' => 'Unable to fetch weather data for the specified city.',
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
            'message' => 'Weather data retrieved successfully',
            'data' => $weatherData,
        ]);
    }

    /**
     * Get weather search history for the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getSearchHistory(Request $request): JsonResponse
    {
        $history = $request->user()->weatherSearches()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'message' => 'Search history retrieved successfully',
            'data' => $history,
        ]);
    }

    /**
     * Clear weather search history for the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function clearSearchHistory(Request $request): JsonResponse
    {
        $request->user()->weatherSearches()->delete();

        return response()->json([
            'message' => 'Search history cleared successfully',
        ]);
    }
}
