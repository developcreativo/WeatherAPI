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
     * Test Swagger JSON exists in the storage path.
     */
    public function test_swagger_json_contains_endpoints(): void
    {
        // Verify the JSON file exists in the storage path where l5-swagger generates it
        $jsonPath = storage_path('api-docs/api-docs.json');
        $this->assertFileExists($jsonPath);
        
        // Read the contents
        $jsonContent = file_get_contents($jsonPath);
        $this->assertNotEmpty($jsonContent);
        
        $apiDocs = json_decode($jsonContent, true);
        $this->assertIsArray($apiDocs);
        
        // Verify basic structure exists
        $this->assertArrayHasKey('openapi', $apiDocs);
        $this->assertArrayHasKey('info', $apiDocs);
        $this->assertArrayHasKey('paths', $apiDocs);
    }
}
