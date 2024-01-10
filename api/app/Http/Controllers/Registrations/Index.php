<?php

namespace App\Http\Controllers\Registrations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Fencer;
use App\Models\Registration;
use App\Models\Schemas\Registrations as RegistrationsSchema;
use Auth;

class Index extends Controller
{
    /**
     * Event registration overview
     *
     * @OA\Get(
     *     path = "/registrations",
     *     @OA\Parameter(
     *         in = "query",
     *         name = "event",
     *         description = "Event identifier to get registration data on",
     *         required = false,
     *         style = "form",
     *         explode = "false",
     *         @OA\Schema(
     *             type = "integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         in = "query",
     *         name = "country",
     *         description = "Country identifier to get authorization data on",
     *         required = false,
     *         style = "form",
     *         explode = "false",
     *         @OA\Schema(
     *             type = "integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response = "200",
     *         description = "List of registrations",
     *         ref="#/components/schemas/Registrations"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $event = $request->get('eventObject');
        if (empty($event) || !$event->exists || get_class($event) != Event::class) {
            $this->authorize("not/ever");
        }

        if ($request->user()->can("viewRegistrations", $event)) {
            $country = $request->get('countryObject');
            // superhod cannot set org roles
            $isOrganiser = $request->user()->hasRole(['sysop', 'organiser:' . $event->getKey(), 'registrar:' . $event->getKey()]);

            // country should be implicitely or explicitely set, except for organisers
            if (empty($country) && !$isOrganiser) {
                $this->authorize("not/ever");
            }
            else {
                $getAll = $request->get('all');
                if ($getAll) {
                    $this->authorize('organise', $event);
                }
                $retval = new RegistrationsSchema();
                $query = Registration::where('registration_mainevent', $event->getKey());
                if (!$getAll) {
                    $query = $query->where('registration_country', (empty($country) ? null : $country->getKey()));
                }
                $rows = $query->with('fencer')->get();
                
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
