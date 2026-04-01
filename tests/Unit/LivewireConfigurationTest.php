<?php

namespace Tests\Unit;

use Tests\TestCase;

class LivewireConfigurationTest extends TestCase
{
    public function test_livewire_is_locked_to_class_based_components(): void
    {
        $this->assertSame([], config('livewire.component_locations'));
        $this->assertSame([
            'layouts' => resource_path('views/layouts'),
        ], config('livewire.component_namespaces'));
        $this->assertSame('layouts::blank', config('livewire.component_layout'));
        $this->assertSame('class', config('livewire.make_command.type'));
        $this->assertFalse((bool) config('livewire.make_command.emoji'));
    }

    public function test_livewire_assets_are_manually_loaded_by_layouts(): void
    {
        $this->assertFalse((bool) config('livewire.inject_assets'));
        $this->assertStringContainsString('@livewireStyles', file_get_contents(resource_path('views/layouts/frontend/app.blade.php')) ?: '');
        $this->assertStringContainsString('@livewireScripts', file_get_contents(resource_path('views/layouts/frontend/app.blade.php')) ?: '');
        $this->assertStringContainsString('@livewireStyles', file_get_contents(resource_path('views/layouts/backend/app.blade.php')) ?: '');
        $this->assertStringContainsString('@livewireScripts', file_get_contents(resource_path('views/layouts/backend/app.blade.php')) ?: '');
        $this->assertStringContainsString('@livewireStyles', file_get_contents(resource_path('views/layouts/blank.blade.php')) ?: '');
        $this->assertStringContainsString('@livewireScripts', file_get_contents(resource_path('views/layouts/blank.blade.php')) ?: '');
    }
}
