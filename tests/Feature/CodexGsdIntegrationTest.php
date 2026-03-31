<?php

namespace Tests\Feature;

use Tests\TestCase;

class CodexGsdIntegrationTest extends TestCase
{
    public function test_project_local_codex_gsd_integration_is_wired(): void
    {
        $configPath = base_path('.codex/config.toml');
        $config = file_get_contents($configPath);

        $this->assertFileExists($configPath);
        $this->assertStringContainsString('codex_hooks = true', $config);
        $this->assertStringContainsString(sprintf('cwd = "%s"', base_path()), $config);
        $this->assertStringContainsString('[agents.project-gsd-coordinator]', $config);
        $this->assertStringContainsString('.codex/agents/project-gsd-coordinator.toml', $config);

        $hooksPath = base_path('.codex/hooks.json');
        $hooks = json_decode(file_get_contents($hooksPath), true, flags: JSON_THROW_ON_ERROR);

        $this->assertFileExists($hooksPath);
        $this->assertArrayHasKey('SessionStart', $hooks['hooks']);
        $this->assertArrayHasKey('UserPromptSubmit', $hooks['hooks']);
        $encodedHooks = json_encode($hooks, JSON_THROW_ON_ERROR);

        $this->assertStringContainsString('gsd-update-check.cjs', $encodedHooks);
        $this->assertStringContainsString('gsd-session-start.cjs', $encodedHooks);
        $this->assertStringContainsString('gsd-prompt-router.cjs', $encodedHooks);

        $this->assertFileExists(base_path('.codex/hooks/gsd-session-start.cjs'));
        $this->assertFileExists(base_path('.codex/hooks/gsd-prompt-router.cjs'));
        $this->assertFileExists(base_path('.codex/get-shit-done/hooks/gsd-update-check.cjs'));
        $this->assertFileExists(base_path('.codex/skills/gsd-new-project/SKILL.md'));
        $this->assertFileExists(base_path('.codex/skills/gsd-project-default/SKILL.md'));
        $this->assertFileExists(base_path('.codex/skills/gsd-project-default/agents/openai.yaml'));

        $this->assertTrue(is_link(base_path('.agents/skills/gsd-new-project')));
        $this->assertTrue(is_link(base_path('.agents/skills/gsd-project-default')));
        $this->assertTrue(is_link(base_path('CLAUDE.md')));
        $this->assertSame('AGENTS.md', readlink(base_path('CLAUDE.md')));
    }
}
