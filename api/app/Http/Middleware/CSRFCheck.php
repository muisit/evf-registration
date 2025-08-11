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
    private $except = ['/fe/*'];

    public function handle(Request $request, \Closure $next): mixed
    {
        $method = $request->getRealMethod();

        if ($method === 'POST' && !$this->inExceptArray($request)) {
            $csrfToken = $request->header('X-CSRF-Token');

            if (empty($csrfToken) || $csrfToken != csrf_token()) {
                \Log::debug("CSRF testing $csrfToken vs " . csrf_token() . ' fails');
                throw new BadRequestHttpException('X-CSRF-Token header must be set');
            }
        }

        return $next($request);
    }

    protected function inExceptArray($request)
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->fullUrlIs($except) || $request->is($except)) {
                return true;
            }
        }

        return false;
    }

}
