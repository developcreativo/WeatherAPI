<?php

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SwaggerDocumentationTest extends TestCase
{
    /**
     * Test Swagger documentation is accessible.
     */
    public function test_swagger_documentation_is_accessible(): void
    {
        $response = $this->get('api/documentation');
        $response->assertStatus(200);
    }

    /**
     * Test Swagger JSON is valid and contains our endpoints.
     */
    public function test_swagger_json_contains_endpoints(): void
    {
        $response = $this->get('api/documentation/api-docs.json');
        $response->assertStatus(200);
        
        $jsonContent = $response->getContent();
        $apiDocs = json_decode($jsonContent, true);
        
        // Verify the API title is correct
        $this->assertEquals("Laravel Weather API", $apiDocs['info']['title']);
        
        // Verify our main endpoint paths exist
        $this->assertArrayHasKey('/auth/register', $apiDocs['paths']);
        $this->assertArrayHasKey('/auth/login', $apiDocs['paths']);
        $this->assertArrayHasKey('/weather/current', $apiDocs['paths']);
        $this->assertArrayHasKey('/favorites', $apiDocs['paths']);
    }
}
