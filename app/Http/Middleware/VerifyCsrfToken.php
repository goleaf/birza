<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Foundation\Application;

class VerifyCsrfToken extends Middleware
{
    public function __construct(?Application $app = null, ?Encrypter $encrypter = null)
    {
        parent::__construct(
            $app ?? app(Application::class),
            $encrypter ?? app(Encrypter::class)
        );
    }

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}
