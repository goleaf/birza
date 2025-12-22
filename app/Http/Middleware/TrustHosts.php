<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;
use Illuminate\Contracts\Foundation\Application;

class TrustHosts extends Middleware
{
    public function __construct(?Application $app = null)
    {
        parent::__construct($app ?? app(Application::class));
    }

    /**
     * Get the host patterns that should be trusted.
     *
     * @return array<int, string|null>
     */
    public function hosts(): array
    {
        return [
            $this->allSubdomainsOfApplicationUrl(),
        ];
    }
}
