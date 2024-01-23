<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Schemas\EventRoles as EventRolesSchema;

class Roles extends Controller
{
    /**
     * List of event roles and eligible users
     *
     * @OA\Get(
     *     path = "/events/roles",
     *     @OA\Response(
     *         response = "200",
     *         description = "List of event specific roles and eligible users",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/EventRoles")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $event = $request->get('eventObject');
        $this->authorize('update', $event);
        return response()->json(new EventRolesSchema($event));
    }
}
