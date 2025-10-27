<?php

namespace App\Http\Controllers\FE\Events;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event as Model;
use App\Models\Competition;
use App\Models\Result;
use App\Models\Requests\FERequest;
use App\Models\Schemas\Competition as Schema;
use App\Models\Schemas\FE\WPResponse;
use DB;

class Competitions extends Controller
{
    /**
     * List using sorting and filtering
     */
    public function index(Request $request)
    {
        if ($request->get('path') != '/events/competitions') {
            $this->authorize('not/ever');
        }

        $model = (object)$request->get('model');
        if (empty($model)) {
            $this->authorize('not/ever');
        }
        $event = Model::find($model->id);
        if (empty($event)) {
            $this->authorize('not/ever');
        }

        $competitions = $event->competitions()->orderBy('competition_weapon')->orderBy('competition_category')->get()->map(function ($c) {
            $c->result_total = $c->results()->count();
            return $c;
        });
        $total = count($competitions);
        $data = $competitions->map(fn ($item) => new Schema($item, false));
        return response()->json(new WPResponse(["list" => $data, "total" => $total]));
    }

    private function filterQuery($model)
    {
        $event = Model::find($model->id);
        if (empty($event)) {
            return null;
        }
        return $event->competitions();
    }
}
