<?php

namespace Tests\Feature\Controllers\Frontend\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_controller_exists(): void
    {
        $this->assertTrue(
            class_exists(\App\Http\Controllers\Frontend\Auth\AuthController::class)
        );
    }
}

