<?php

namespace App\Http\Controllers\Events;

use App\Models\SideEvent;
use App\Support\Services\ParticipantListService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Download extends Controller
{
    /**
     * Create a CSV participant list for the current event
     *
     * @OA\Get(
     *     path = "/events/csv",
     * )
     */
    public function asCSV(Request $request, int $sideEventId)
    {
        $event = $request->get('eventObject');
        $this->authorize('accredit', $event);
        $sideEvent = SideEvent::find($sideEventId);
        if (empty($sideEvent) || $sideEvent->event_id != $event->getKey()) {
            $this->authorize('not/ever');
        }
        $filename = 'participants_' . $sideEvent->title . '.csv';
        (new ParticipantListService($sideEvent))->asCSV($filename);
    }

    /**
     * Create an XML participant list for the current event
     *
     * @OA\Get(
     *     path = "/events/xml",
     * )
     */
    public function asXML(Request $request, int $sideEventId)
    {
        $event = $request->get('eventObject');
        $this->authorize('accredit', $event);
        $sideEvent = SideEvent::find($sideEventId);
        if (empty($sideEvent) || $sideEvent->event_id != $event->getKey()) {
            $this->authorize('not/ever');
        }

        $xml_file_name = $event->event_name . "." . $sideEvent->title . ".xml";
        return (new ParticipantListService($sideEvent))->asXML($xml_file_name);
    }
}
