<?php

namespace App\Http\Controllers\Accreditations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accreditation;
use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use App\Jobs\CheckBadge;

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
    public function index(Request $request, int $fencerId, int $templateId)
    {
        $event = $request->get('eventObject');
        if (empty($event) || !$event->exists || get_class($event) != Event::class) {
            $this->authorize("not/ever");
        }
        $accreditation = $this->findAccreditation($event, $fencerId, $templateId);
        if (empty($accreditation) || $accreditation->event_id != $event->getKey()) {
            $this->authorize('not/ever');
        }
        $this->authorize('view', $accreditation);

        if (!empty($accreditation->is_dirty)) {
            $accreditation = $this->regenerate($accreditation);
            if (empty($accreditation)) {
                // this causes an ugly break in the front-end, where the
                // 404 is displayed instead of the SPA, forcing the user to
                // move back using the browser buttons and losing any
                // existing selections...
                abort(404);
            }
        }

        $filename = $accreditation->path(false);
        if (Storage::disk('local')->exists($filename)) {
            return Storage::download($filename, $accreditation->template->name . '.pdf', ['Content-type' => 'application/pdf']);
        }
        else {
            \Log::debug("file " . $filename . " does not appear to exist");
        }
        abort(404);
    }

    private function findAccreditation(Event $event, int $fencerId, int $templateId)
    {
        return Accreditation::where('event_id', $event->getKey())
            ->where('fencer_id', $fencerId)
            ->where('template_id', $templateId)
            ->first();
    }

    private function regenerate(Accreditation $accreditation)
    {
        \Log::debug("regenerating accreditation because it is dirty");
        $job = new CheckBadge($accreditation->fencer_id, $accreditation->event_id);
        $job->handleSynchronous();

        return $this->findAccreditation($accreditation->event, $accreditation->fencer_id, $accreditation->template_id);
    }
}
