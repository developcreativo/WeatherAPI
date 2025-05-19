<?php

namespace Tests\Feature\API;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tests\WithPermissions;

class PermissionsTest extends TestCase
{
    use RefreshDatabase, WithPermissions;

    /**
     * Test a user with permission can access protected routes.
     */
    public function test_user_with_permission_can_access_route(): void
    {
        // Create user with permission
        $user = User::factory()->create();
        $this->assignPermissions($user, ['view weather']);
        $token = $user->createToken('test-token')->plainTextToken;

        // Make the API request
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('api/weather/current', [
            'city' => 'London',
        ]);

        // Should succeed because user has permission
        $response->assertStatus(200);
    }

    /**
     * Test a user without permission cannot access protected routes.
     */
    public function test_user_without_permission_cannot_access_route(): void
    {
        // Create user without any permissions
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // Mock weather service to avoid actual API calls
        $this->mock(
            \App\Services\WeatherService::class,
            function ($mock) {
                $mock->shouldReceive('getCurrentWeather')
                    ->andReturn([
                        'city' => 'London',
                        'country' => 'United Kingdom',
                        'temperature' => 15.5,
                        'condition' => 'Cloudy',
                        'wind_speed' => 10.0,
                        'humidity' => 75,
                        'local_time' => now()->toDateTimeString(),
                    ]);
            }
        );

        // Make the API request
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('api/weather/current', [
            'city' => 'London',
        ]);

        // Should fail because user doesn't have permission
        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Unauthorized',
        ]);
    }

    /**
     * Test admin role has all permissions.
     */
    public function test_admin_role_has_all_permissions(): void
    {
        // Create role and permissions
        $adminRole = Role::create(['name' => 'admin']);
        $permissions = [
            'view weather',
            'view history',
            'clear history',
            'view favorites',
            'create favorites',
            'delete favorites',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        $adminRole->syncPermissions($permissions);

        // Create admin user
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Verify admin has all permissions
        foreach ($permissions as $permission) {
            $this->assertTrue($admin->hasPermissionTo($permission));
        }
    }

    /**
     * Test user role has limited permissions.
     */
    public function test_user_role_has_limited_permissions(): void
    {
        // Create user role with limited permissions
        $userRole = Role::create(['name' => 'user']);
        $userPermissions = [
            'view weather',
            'view history',
            'view favorites',
        ];

        $adminPermissions = [
            'clear history',
            'create favorites',
            'delete favorites',
        ];

        foreach (array_merge($userPermissions, $adminPermissions) as $permission) {
            Permission::create(['name' => $permission]);
        }

        $userRole->syncPermissions($userPermissions);

        // Create regular user
        $user = User::factory()->create();
        $user->assignRole('user');

        // Verify user has only user permissions
        foreach ($userPermissions as $permission) {
            $this->assertTrue($user->hasPermissionTo($permission));
        }

        // Verify user doesn't have admin permissions
        foreach ($adminPermissions as $permission) {
            $this->assertFalse($user->hasPermissionTo($permission));
        }
    }
}
