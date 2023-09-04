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
        if ($request->has('event')) {
            $event = Event::where('event_id', $request->get('event'))->first();
            if ($event->exists) {
                $request->merge(['eventObject' => $event]);
            }
        }
        return $next($request);
    }
}
