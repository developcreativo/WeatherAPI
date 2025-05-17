<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    /**
     * The base URL for Weather API
     *
     * @var string
     */
    protected string $baseUrl = 'https://api.weatherapi.com/v1';

    /**
     * Get current weather data for a city
     *
     * @param string $city The name of the city
     * @return array|null The weather data or null on failure
     */
    public function getCurrentWeather(string $city): ?array
    {
        $cacheKey = "weather_data_{$city}";
        
        // Try to get data from cache first (valid for 30 minutes)
        return Cache::remember($cacheKey, 60 * 30, function () use ($city) {
            try {
                $response = Http::get("{$this->baseUrl}/current.json", [
                    'key' => config('services.weather_api.key'),
                    'q' => $city,
                ]);
                
                if ($response->successful()) {
                    return $this->formatWeatherData($response->json());
                }
                
                Log::error('Weather API error', [
                    'city' => $city,
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);
                
                return null;
            } catch (RequestException $e) {
                Log::error('Weather API request failed', [
                    'city' => $city,
                    'error' => $e->getMessage(),
                ]);
                
                return null;
            }
        });
    }

    /**
     * Format the weather data from the API response
     *
     * @param array $data The raw API response data
     * @return array The formatted weather data
     */
    protected function formatWeatherData(array $data): array
    {
        $current = $data['current'] ?? [];
        $location = $data['location'] ?? [];
        
        return [
            'city' => $location['name'] ?? '',
            'country' => $location['country'] ?? '',
            'temperature' => $current['temp_c'] ?? 0,
            'condition' => $current['condition']['text'] ?? '',
            'wind_speed' => $current['wind_kph'] ?? 0,
            'humidity' => $current['humidity'] ?? 0,
            'local_time' => $location['localtime'] ?? now()->toDateTimeString(),
            'raw_data' => $data,
        ];
    }
}
