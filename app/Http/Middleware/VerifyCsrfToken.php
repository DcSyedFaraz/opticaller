<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'call-data',
        'dial/callback',
        'admin/join-conference',
        'dial/admincallback_data',
        'dial/callbackUser',
        'conference/join-conference',
    ];
}
