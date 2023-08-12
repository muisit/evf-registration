<?php 

namespace App\Http\Middleware;

use Closure;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (app()->environment('local')) {
            $headers = [
                'Access-Control-Allow-Origin'      => $this->getOrigin(),
                'Access-Control-Allow-Methods'     => 'POST, GET, OPTIONS, PUT, DELETE',
                'Access-Control-Allow-Credentials' => 'true',
                'Access-Control-Max-Age'           => '86400',
                'Access-Control-Allow-Headers'     => 'Content-Type, Authorization, X-Requested-With, X-CSRF-Token',
                'Vary'                             => 'Origin'
            ];

            if ($request->isMethod('OPTIONS')) {
                return response()->json('{"method":"OPTIONS"}', 200, $headers);
            }

            $response = $next($request);
            foreach ($headers as $key => $value) {
                $response->header($key, $value);
            }

            return $response;
        }
        else {
            return $next($request);
        }
    }

    private function getOrigin()
    {
        if (isset($_SERVER['HTTP_ORIGIN'])) return $_SERVER['HTTP_ORIGIN'];
        if (isset($_SERVER['HTTP_HOST'])) return $_SERVER["REQUEST_SCHEME"] . '://' . $_SERVER['HTTP_HOST'];
        return '*';
    }
}
