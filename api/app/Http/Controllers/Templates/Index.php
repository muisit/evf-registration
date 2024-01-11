<?php

namespace App\Http\Controllers\Templates;

use App\Models\Event;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccreditationTemplate;
use App\Models\Schemas\AccreditationTemplate as TemplateSchema;

class Index extends Controller
{
    /**
     * List of templates
     *
     * @OA\Get(
     *     path = "/templates",
     *     @OA\Response(
     *         response = "200",
     *         description = "List of accessible templates",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/AccreditationTemplate")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        \Log::debug("calling templates/index");
        $event = $request->get('eventObject');
        if (empty($event) || !$event->exists || get_class($event) != Event::class) {
            abort(404);
        }
        $this->authorize('viewAny', AccreditationTemplate::class);

        $templates = AccreditationTemplate::where('is_default', 'Y')->orWhere('event_id', $event->getKey())->get();
        $retval = [];
        foreach ($templates as $template) {
            if ($request->user()->can('view', $template)) {
                $retval[] = new TemplateSchema($template);
            }
        }
        return response()->json($retval);
    }
}
