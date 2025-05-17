<?php

namespace Tests\Feature\Services;

use App\Services\WeatherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherServiceTest extends TestCase
{
    use WithFaker;

    protected WeatherService $weatherService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->weatherService = new WeatherService();
        
        // Set a fake API key for testing
        Config::set('services.weather_api.key', 'test-api-key');
    }

    /**
     * Test that the service can fetch weather data for a valid city.
     */
    public function test_can_get_current_weather_for_valid_city(): void
    {
        // Arrange: Mock the HTTP request
        Http::fake([
            'api.weatherapi.com/v1/current.json*' => Http::response([
                'location' => [
                    'name' => 'London',
                    'country' => 'United Kingdom',
                    'localtime' => '2025-05-17 18:30',
                ],
                'current' => [
                    'temp_c' => 15.5,
                    'condition' => [
                        'text' => 'Partly cloudy',
                    ],
                    'wind_kph' => 10.2,
                    'humidity' => 75,
                ],
            ], 200),
        ]);

        // Act: Call the service method
        $result = $this->weatherService->getCurrentWeather('London');

        // Assert: Check the result
        $this->assertIsArray($result);
        $this->assertEquals('London', $result['city']);
        $this->assertEquals('United Kingdom', $result['country']);
        $this->assertEquals(15.5, $result['temperature']);
        $this->assertEquals('Partly cloudy', $result['condition']);
        $this->assertEquals(10.2, $result['wind_speed']);
        $this->assertEquals(75, $result['humidity']);
    }

    /**
     * Test that the service returns null for an invalid city.
     */
    public function test_returns_null_for_invalid_city(): void
    {
        // Arrange: Mock the HTTP request to simulate an error
        Http::fake([
            'api.weatherapi.com/v1/current.json*' => Http::response([
                'error' => [
                    'code' => 1006,
                    'message' => 'No matching location found.',
                ],
            ], 400),
        ]);

        // Act: Call the service method
        $result = $this->weatherService->getCurrentWeather('InvalidCityName');

        // Assert: Check that the result is null
        $this->assertNull($result);
    }

    /**
     * Test that weather data is cached.
     */
    public function test_weather_data_is_cached(): void
    {
        // Arrange: Mock the HTTP request and clear the cache
        Cache::flush();
        
        Http::fake([
            'api.weatherapi.com/v1/current.json*' => Http::response([
                'location' => [
                    'name' => 'New York',
                    'country' => 'United States of America',
                    'localtime' => '2025-05-17 18:30',
                ],
                'current' => [
                    'temp_c' => 22.5,
                    'condition' => [
                        'text' => 'Sunny',
                    ],
                    'wind_kph' => 5.2,
                    'humidity' => 45,
                ],
            ], 200),
        ]);

        // Act: Call the service method twice
        $this->weatherService->getCurrentWeather('New York');
        $this->weatherService->getCurrentWeather('New York');

        // Assert: Check that only one HTTP request was made
        Http::assertSentCount(1);
    }
}
