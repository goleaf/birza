<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;
use Illuminate\Contracts\Foundation\Application;

class PreventRequestsDuringMaintenance extends Middleware
{
    public function __construct(?Application $app = null)
    {
        parent::__construct($app ?? app(Application::class));
    }

    /**
     * The URIs that should be reachable while maintenance mode is enabled.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}
