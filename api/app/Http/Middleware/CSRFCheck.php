<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Ensures the request is not vulnerable to cross-site request forgery
 * by checking the bearer token
 */
class CSRFCheck
{
    public function handle(Request $request, \Closure $next): mixed
    {
        $method = $request->getRealMethod();

        if ($method === 'POST') {
            $csrfToken = $request->header('X-CSRF-Token');

            if (empty($csrfToken) || $csrfToken != csrf_token()) {
                \Log::debug("testing $csrfToken vs " . csrf_token() . ' fails');
                throw new BadRequestHttpException('X-CSRF-Token header must be set');
            }
        }

        return $next($request);
    }
}
