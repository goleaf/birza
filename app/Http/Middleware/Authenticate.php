<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    public function __construct(?AuthFactory $auth = null)
    {
        parent::__construct($auth ?? app(AuthFactory::class));
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    public function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('home');
    }
}
