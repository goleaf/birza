<?php

namespace Tests\Unit\Support;

use App\Support\SafeMarkdown;
use PHPUnit\Framework\TestCase;

class SafeMarkdownTest extends TestCase
{
    public function test_it_renders_markdown_without_embedded_html_or_unsafe_links(): void
    {
        $html = SafeMarkdown::render(
            "# Safe heading\n\n<script>alert('xss')</script>\n\n[unsafe](javascript:alert('xss'))"
        )->toHtml();

        $this->assertStringContainsString('<h1>Safe heading</h1>', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('javascript:', $html);
    }
}
