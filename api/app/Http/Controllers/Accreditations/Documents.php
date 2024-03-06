<?php

namespace App\Http\Controllers\Accreditations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccreditationDocument;
use App\Models\Schemas\AccreditationDocument as DocumentSchema;
use Auth;

class Documents extends Controller
{
    /**
     * List of accreditation documents currently pending for the event
     *
     * @OA\Get(
     *     path = "/accreditations/documents",
     *     @OA\Response(
     *         response = "200",
     *         description = "List of documents",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/AccreditationDocument")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $retval = [];
        $event = $request->get('eventObject');
        if (!empty($event) && $request->user()->can('viewAny', AccreditationDocument::class)) {
            // only list the documents that are not checked-out yet
            $documents = AccreditationDocument::whereIn(
                'status',
                [
                    AccreditationDocument::STATUS_CREATED,
                    AccreditationDocument::STATUS_PROCESSING,
                    AccreditationDocument::STATUS_PROCESSED_GOOD,
                    AccreditationDocument::STATUS_PROCESSED_ERROR,
                ]
            )
                ->joinRelationshipUsingAlias('accreditation', 'a')
                ->where('a.event_id', $event->getKey())
                ->orderBy(AccreditationDocument::tableName() . '.created_at', 'desc')
                ->get();
            foreach ($documents as $doc) {
                $retval[] = new DocumentSchema($doc);
            }

            $documents = AccreditationDocument::where('status', AccreditationDocument::STATUS_CHECKOUT)
                ->joinRelationshipUsingAlias('accreditation', 'a')
                ->where('a.event_id', $event->getKey())
                ->orderBy(AccreditationDocument::tableName() . '.checkout', 'desc')
                ->limit(10)
                ->get();
            foreach ($documents as $doc) {
                $retval[] = new DocumentSchema($doc);
            }
        }
        return response()->json($retval);
    }
}
