<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_home_route_is_registered(): void
    {
        $route = app('router')->getRoutes()->match(Request::create('/', 'GET'));

        $this->assertSame('/', '/' . ltrim($route->uri(), '/'));
    }
}
