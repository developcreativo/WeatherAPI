<?php

namespace Tests\Feature\API;

use App\Models\User;
use App\Services\WeatherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Mockery;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Tests\WithPermissions;

class WeatherControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker, WithPermissions;

    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and token for testing
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
        
        // Assign necessary permissions
        $this->assignPermissions($this->user, [
            'view weather',
            'view history',
            'clear history'
        ]);
    }

    /**
     * Test getting current weather data.
     */
    public function test_can_get_current_weather_data(): void
    {
        // Mock the WeatherService
        $weatherServiceMock = Mockery::mock(WeatherService::class);
        $weatherServiceMock->shouldReceive('getCurrentWeather')
            ->once()
            ->with('London')
            ->andReturn([
                'city' => 'London',
                'country' => 'United Kingdom',
                'temperature' => 15.5,
                'condition' => 'Cloudy',
                'wind_speed' => 10.0,
                'humidity' => 75,
                'local_time' => now()->toDateTimeString(),
                'raw_data' => ['some' => 'data'],
            ]);

        $this->app->instance(WeatherService::class, $weatherServiceMock);

        // Make the API request
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('api/weather/current', [
            'city' => 'London'
        ]);

        // Check the response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'city',
                    'country',
                    'temperature',
                    'condition',
                    'wind_speed',
                    'humidity',
                    'local_time',
                ],
            ]);

        // Check that the weather search was saved to the database
        $this->assertDatabaseHas('weather_searches', [
            'user_id' => $this->user->id,
            'city' => 'London',
        ]);
    }

    /**
     * Test getting current weather data for invalid city.
     */
    public function test_returns_error_for_invalid_city(): void
    {
        // Mock the WeatherService
        $weatherServiceMock = Mockery::mock(WeatherService::class);
        $weatherServiceMock->shouldReceive('getCurrentWeather')
            ->once()
            ->with('InvalidCity')
            ->andReturn(null);

        $this->app->instance(WeatherService::class, $weatherServiceMock);

        // Make the API request
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('api/weather/current', [
            'city' => 'InvalidCity'
        ]);

        // Check the response
        $response->assertStatus(404)
            ->assertJson([
                'message' => __('weather.weather_data_failure'),
            ]);
    }

    /**
     * Test getting weather search history.
     */
    public function test_can_get_search_history(): void
    {
        // Create some weather search history
        $this->user->weatherSearches()->createMany([
            [
                'city' => 'London',
                'country' => 'United Kingdom',
                'temperature' => 15.5,
                'condition' => 'Cloudy',
                'wind_speed' => 10.0,
                'humidity' => 75,
                'local_time' => now()->toDateTimeString(),
            ],
            [
                'city' => 'Paris',
                'country' => 'France',
                'temperature' => 18.0,
                'condition' => 'Sunny',
                'wind_speed' => 5.0,
                'humidity' => 65,
                'local_time' => now()->toDateTimeString(),
            ],
        ]);

        // Make the API request
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('api/weather/history');

        // Check the response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'user_id',
                            'city',
                            'country',
                            'temperature',
                            'condition',
                            'wind_speed',
                            'humidity',
                            'local_time',
                        ],
                    ],
                ],
            ]);

        // Verify we have the expected number of records
        $this->assertEquals(2, count($response->json('data.data')));
    }

    /**
     * Test clearing search history.
     */
    public function test_can_clear_search_history(): void
    {
        // Create some weather search history
        $this->user->weatherSearches()->createMany([
            [
                'city' => 'London',
                'country' => 'United Kingdom',
                'temperature' => 15.5,
                'condition' => 'Cloudy',
                'wind_speed' => 10.0,
                'humidity' => 75,
                'local_time' => now()->toDateTimeString(),
            ],
            [
                'city' => 'Paris',
                'country' => 'France',
                'temperature' => 18.0,
                'condition' => 'Sunny',
                'wind_speed' => 5.0,
                'humidity' => 65,
                'local_time' => now()->toDateTimeString(),
            ],
        ]);

        // Verify we have records to begin with
        $this->assertDatabaseCount('weather_searches', 2);

        // Make the API request to clear history
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson('api/weather/history');

        // Check the response
        $response->assertStatus(200)
            ->assertJson([
                'message' => __('weather.search_history_clear_success'),
            ]);

        // Verify the records were deleted
        $this->assertDatabaseCount('weather_searches', 0);
    }
}
