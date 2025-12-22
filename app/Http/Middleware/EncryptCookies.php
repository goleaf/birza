<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;
use Illuminate\Contracts\Encryption\Encrypter;

class EncryptCookies extends Middleware
{
    public function __construct(?Encrypter $encrypter = null)
    {
        parent::__construct($encrypter ?? app(Encrypter::class));
    }

    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}
