<?php

namespace Tests\Unit\Config;

use Tests\TestCase;

class SecurityDefaultsTest extends TestCase
{
    public function test_session_and_cors_defaults_are_secure(): void
    {
        $this->assertTrue(config('session.encrypt'));
        $this->assertTrue(config('session.secure'));
        $this->assertNotContains('*', config('cors.allowed_methods'));
        $this->assertNotContains('*', config('cors.allowed_origins'));
        $this->assertContains(config('app.url'), config('cors.allowed_origins'));
    }
}
