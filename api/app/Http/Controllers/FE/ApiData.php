<?php

namespace App\Http\Controllers\FE;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Schemas\Event as Schema;
use App\Models\Schemas\FE\Response;
use App\Jobs\CreateRanking;
use App\Support\Services\BasicDataService;
use Illuminate\Http\Request;

class ApiData extends Controller
{
    /**
     * Return all required api data for the backoffice main page
     *
     * @OA\Post(
     *     path = "/apidata",
     *     @OA\Response(
     *         response = "200",
     *         description = "Data returned",
     *         @OA\JsonContent(ref="#/components/schemas/ReturnStatus")
     *     )
     *     @OA\Response(
     *         response = "404",
     *         description = "No data found",
     *         @OA\JsonContent(ref="#/components/schemas/ReturnStatus")
     *     )
     * )
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Event::class);

        $model = $request->get('model');
        if (!empty($model['cutoff'])) {
            BasicDataService::setCutOff($model['cutoff']);
        }
        if (!empty($model['apikey'])) {
            BasicDataService::setAPIKey($model['apikey']);
        }
        if (!empty($model['apiuser'])) {
            BasicDataService::setAPIUser($model['apiuser']);
        }

        $response = [
            'cutoff' => BasicDataService::getCutOff(),
            'apikey' => BasicDataService::getAPIKey(),
            'apiuser' => BasicDataService::getAPIUser(),
            'events' => BasicDataService::rankedEvents()->map(fn ($e) => new Schema($e))
        ];

        return response()->json(new Response('ok', null, $response));
    }
}
