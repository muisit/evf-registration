<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use App\Models\Event;

class GlobalParameters
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        if ($request->has('event_id')) {
            $event = Event::find($request->get('event_id'));
            if ($event->exists) {
                $request->merge(['event' => $event]);
            }
        }
        return $next($request);
    }
}
