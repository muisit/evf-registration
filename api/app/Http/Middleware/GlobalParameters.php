<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Event;
use App\Support\Services\DefaultCountryService;
use App\Support\Services\DefaultEventService;

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
        $this->determineEvent($request);
        $this->determineCountry($request);
        return $next($request);
    }

    private function determineEvent(Request $request)
    {
        // see if the user is linked to a specific event
        if (!empty($request->user())) {
            $event = DefaultEventService::determineEvent($request->user());
        }

        if (empty($event)) {
            if ($request->has('event')) {
                $event = Event::where('event_id', $request->get('event'))->first();
            }
        }

        if (!empty($event) && !$event->isFinished()) {
            $request->merge(['eventObject' => $event]);
        }
    }

    private function determineCountry(Request $request)
    {
        $country = null;
        if (!empty($request->user())) {
            $country = DefaultCountryService::determineCountry($request->user());
        }

        if (empty($country)) {
            // no country roles specified for the user, which means the user is either
            // unrestricted (sysop, superhod, organisation), or not authorised at all
            // The latter case will be blocked by policies
            if ($request->has('country')) {
                $country = Country::where("country_id", $request->get('country'))->first();
            }
        }

        if (!empty($country)) {
            $request->merge(['countryObject' => $country]);
        }
    }
}
