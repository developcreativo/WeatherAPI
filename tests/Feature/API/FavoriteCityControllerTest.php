<?php

namespace Tests\Feature\API;

use App\Models\FavoriteCity;
use App\Models\User;
use App\Services\WeatherService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;
use Tests\WithPermissions;

class FavoriteCityControllerTest extends TestCase
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
            'view favorites',
            'create favorites',
            'delete favorites'
        ]);
    }

    /**
     * Test adding a city to favorites.
     */
    public function test_can_add_city_to_favorites(): void
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
            ]);

        $this->app->instance(WeatherService::class, $weatherServiceMock);

        // Make the API request
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('api/favorites', [
            'city' => 'London',
        ]);

        // Check the response
        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'city',
                    'country',
                ],
            ]);

        // Check that the favorite city was saved to the database
        $this->assertDatabaseHas('favorite_cities', [
            'user_id' => $this->user->id,
            'city' => 'London',
            'country' => 'United Kingdom',
        ]);
    }

    /**
     * Test adding a city that doesn't exist returns an error.
     */
    public function test_adding_nonexistent_city_returns_error(): void
    {
        // Mock the WeatherService
        $weatherServiceMock = Mockery::mock(WeatherService::class);
        $weatherServiceMock->shouldReceive('getCurrentWeather')
            ->once()
            ->with('NonexistentCity')
            ->andReturn(null);

        $this->app->instance(WeatherService::class, $weatherServiceMock);

        // Make the API request
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('api/favorites', [
            'city' => 'NonexistentCity',
        ]);

        // Check the response
        $response->assertStatus(404)
            ->assertJson([
                'message' => __('weather.weather_data_failure'),
            ]);

        // Check that no favorite city was saved to the database
        $this->assertDatabaseMissing('favorite_cities', [
            'user_id' => $this->user->id,
            'city' => 'NonexistentCity',
        ]);
    }

    /**
     * Test listing favorite cities.
     */
    public function test_can_list_favorite_cities(): void
    {
        // Create some favorite cities
        $this->user->favoriteCities()->createMany([
            [
                'city' => 'London',
                'country' => 'United Kingdom',
            ],
            [
                'city' => 'Paris',
                'country' => 'France',
            ],
        ]);

        // Make the API request
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('api/favorites');

        // Check the response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'city',
                        'country',
                    ],
                ],
            ]);

        // Verify we have the expected number of records
        $this->assertCount(2, $response->json('data'));
    }

    /**
     * Test removing a city from favorites.
     */
    public function test_can_remove_city_from_favorites(): void
    {
        // Create a favorite city
        $favoriteCity = $this->user->favoriteCities()->create([
            'city' => 'London',
            'country' => 'United Kingdom',
        ]);

        // Make the API request
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("api/favorites/{$favoriteCity->id}");

        // Check the response
        $response->assertStatus(200)
            ->assertJson([
                'message' => __('weather.city_favorite_remove_success'),
            ]);

        // Verify the city was removed from favorites
        $this->assertDatabaseMissing('favorite_cities', [
            'id' => $favoriteCity->id,
        ]);
    }

    /**
     * Test that a user cannot remove another user's favorite city.
     */
    public function test_cannot_remove_another_users_favorite_city(): void
    {
        // Create another user and add a favorite city
        $anotherUser = User::factory()->create();
        $favoriteCity = $anotherUser->favoriteCities()->create([
            'city' => 'London',
            'country' => 'United Kingdom',
        ]);

        // Make the API request with our original user's token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("api/favorites/{$favoriteCity->id}");

        // Check the response (should be a 404 as the favorite city doesn't exist for this user)
        $response->assertStatus(404);

        // Verify the city still exists in the database
        $this->assertDatabaseHas('favorite_cities', [
            'id' => $favoriteCity->id,
        ]);
    }
}
