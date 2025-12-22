<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Activity;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_model_exists(): void
    {
        $activity = new Activity();
        $this->assertInstanceOf(Activity::class, $activity);
    }
}

