<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Ensures the request is not vulnerable to cross-site request forgery
 * by checking the bearer token
 */
class CSRFCheck extends VerifyCsrfToken
{
    protected $addHttpCookie = false;

    // do not perform CSRF checks on any of the mobile device API calls
    protected $except = [
        'device/*'
    ];

    protected function runningUnitTests()
    {
        // while convenient, we also include CSRF checks in the tests
        return false;
    }

    protected function getTokenFromRequest($request)
    {
        // application is ONLY using X-CSRF-TOKEN header
        return $request->header('X-CSRF-TOKEN');
    }
}
