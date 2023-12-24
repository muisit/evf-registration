<?php

namespace App\Http\Controllers\Accreditations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;

class Download extends Controller
{
    /**
     * Create a summary document
     *
     * @OA\Get(
     *     path = "/accreditations/download",
     * )
     */
    public function index(Request $request, int $summaryId)
    {
        $document = Document::find($summaryId);
        if (empty($document)) {
            return response()->json(new ReturnStatus('error', 'Unauthorized'), 403);
        }
        $event = $request->get('eventObject');
        $this->authorize('accredit', $event);
        if ($document->event_id != $event->getKey()) {
            return response()->json(new ReturnStatus('error', 'Unauthorized'), 403);
        }
        return Storage::download($document->getPath(false), basename($document->getPath()), ['Content-type' => 'application/pdf']);
    }
}
