<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Setup base test features for all test classes
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Deshabilitar solo el middleware de permisos y roles, pero mantener el middleware de idioma
        // para que las pruebas de multi-idioma funcionen correctamente
        $this->withoutMiddleware([
            // Clases concretas de middleware
            \Spatie\Permission\Middleware\PermissionMiddleware::class,
            \Spatie\Permission\Middleware\RoleMiddleware::class,
            \App\Http\Middleware\PermissionMiddleware::class,
            
            // Aliases de middleware
            'permission',
            'role',
        ]);
    }
}
