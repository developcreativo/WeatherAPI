<?php

namespace Tests\Feature\API;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\WithPermissions;

class MultiLanguageTest extends TestCase
{
    use RefreshDatabase, WithPermissions;

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
            'view history',
            'clear history'
        ]);
    }

    /**
     * Test the API returns responses in English by default.
     */
    public function test_api_defaults_to_english_language(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('api/weather/history');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Search history retrieved successfully',
            ]);
    }

    /**
     * Test the API returns responses in Spanish when requested.
     */
    public function test_api_responds_in_spanish_when_requested(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept-Language' => 'es'
        ])->getJson('api/weather/history');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Historial de búsqueda recuperado con éxito',
            ]);
    }

    /**
     * Test the API falls back to English for unsupported languages.
     */
    public function test_api_falls_back_to_english_for_unsupported_languages(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept-Language' => 'fr' // Unsupported language
        ])->getJson('api/weather/history');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Search history retrieved successfully',
            ]);
    }
}
