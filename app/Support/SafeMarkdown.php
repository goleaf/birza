<?php

namespace App\Support;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class SafeMarkdown
{
    public static function render(?string $markdown): HtmlString
    {
        return new HtmlString(Str::markdown((string) $markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]));
    }
}
