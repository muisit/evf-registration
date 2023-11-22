<?php

namespace App\Http\Controllers\Accreditations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accreditation;
use App\Models\Event;
use Illuminate\Support\Facades\Storage;

class Badge extends Controller
{
    /**
     * Single Accreditation badge
     *
     * @OA\Get(
     *     path = "/accreditations/{accreditationId}/badge",
     *     @OA\Parameter(
     *         in = "query",
     *         name = "accreditationId",
     *         description = "Accreditation identifier to get badge for",
     *         required = false,
     *         style = "form",
     *         explode = "false",
     *         @OA\Schema(
     *             type = "integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response = "200",
     *         description = "Stored file, if available",
     *     )
     * )
     */
    public function index(Request $request, int $accreditationId)
    {
        \Log::debug("getting badge for $accreditationId");
        $event = $request->get('eventObject');
        if (empty($event) || !$event->exists || get_class($event) != Event::class) {
            \Log::debug("no event given" . json_encode($event));
            $this->authorize("not/ever");
        }
        $accreditationObject = Accreditation::find($accreditationId);
        if (empty($accreditationObject) || $accreditationObject->event_id != $event->getKey()) {
            \Log::debug("no valid accreditation");
            $this->authorize('not/ever');
        }
        $this->authorize('view', $accreditationObject);

        $filename = $accreditationObject->path();
        \Log::debug("filename to check is $filename");
        if (Storage::disk('local')->exists($filename)) {
            return Storage::download($filename, $accreditationObject->template->name . '.pdf', ['Content-type' => 'application/pdf']);
        }
        else {
            \Log::debug("file does not appear to exist");
        }
        abort(404);
    }
}
