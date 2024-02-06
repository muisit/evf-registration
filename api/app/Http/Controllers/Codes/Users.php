<?php

namespace App\Http\Controllers\Codes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccreditationUser;
use App\Models\Schemas\AccreditationUser as UserSchema;
use Auth;

class Users extends Controller
{
    /**
     * List of accreditation users for the event
     *
     * @OA\Get(
     *     path = "/codes/users",
     *     @OA\Response(
     *         response = "200",
     *         description = "List of users",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/AccreditationUser")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $event = $request->get('eventObject');
        if (!empty($event) && $request->user()->can('view', $event)) {
            // only list the users linked to an accreditation, not the 'system' users, to prevent accidental lockout
            $users = AccreditationUser::where('event_id', $event->getKey())->whereNot('accreditation_id', null)->with('accreditation')->get();
            $retval = [];
            foreach ($users as $user) {
                $retval[] = new UserSchema($user);
            }
        }
        return response()->json($retval);
    }
}
