<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Route;

class BasicTest extends TestCase
{
    /**
     * A basic test to check if routes are registered correctly.
     */
    public function test_routes_are_registered(): void
    {
        // Dump all registered routes for inspection
        $routes = Route::getRoutes();
        $routeCollection = [];
        $hasApiWeatherRoute = false;
        
        foreach ($routes as $route) {
            $uri = $route->uri();
            $routeCollection[] = [
                'method' => implode('|', $route->methods()),
                'uri' => $uri,
                'name' => $route->getName(),
                'action' => $route->getActionName(),
            ];
            
            // Check if we have the API weather route
            if ($uri === 'api/weather') {
                $hasApiWeatherRoute = true;
            }
        }
        
        // Just to make sure we have the basic web route
        $this->assertTrue(Route::has('sanctum.csrf-cookie'), 'Sanctum routes not registered');
        
        // Output the routes for inspection
        $this->assertNotEmpty($routeCollection, 'No routes registered');
        
        // Output all registered routes for debugging
        print_r($routeCollection);
        
        // Check if our API weather route is registered
        $this->assertTrue($hasApiWeatherRoute, 'API weather route not registered');
    }
}
