<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Fencer;
use App\Models\Registration;
use App\Models\Schemas\Registrations as RegistrationsSchema;
use Auth;

class Registrations extends Controller
{
    /**
     * Event registration overview
     *
     * @OA\Get(
     *     path = "/events/{eventId}/registrations",
     *     @OA\Response(
     *         response = "200",
     *         description = "List of registrations",
     *         ref="#/components/schemas/Registrations"
     *     )
     * )
     */
    public function index(Request $request, string $event)
    {
        $event = Event::where('event_id', $event)->first();
        if (empty($event) || !$event->exists || get_class($event) != Event::class) {
            $this->authorize("not/ever");
        }

        if ($request->user()->can("viewRegistrations", $event)) {
            $country = $request->get('countryObject');
            // superhod cannot set org roles
            $isOrganiser = $request->user()->hasRole(['sysop', 'organiser:' . $event->getKey(), 'reistrar:' . $event->getKey()]);

            // country should be implicitely or explicitely set, except for organisers
            if (empty($country) && !$isOrganiser) {
                $this->authorize("not/ever");
            }
            else {
                $retval = new RegistrationsSchema();
                $rows = Registration::where('registration_mainevent', $event->getKey())
                    ->where('registration_country', (empty($country) ? null : $country->getKey()))
                    ->with('fencer')
                    ->get();
                
                if (!emptyResult($rows)) {
                    foreach ($rows as $row) {
                        $retval->add($row);
                    }
                }
                $retval->finalize();
                return response()->json($retval);
            }
        }
        else {
            $this->authorize("not/ever");
        }
    }
}
