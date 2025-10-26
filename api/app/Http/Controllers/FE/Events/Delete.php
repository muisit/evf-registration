<?php

namespace App\Http\Controllers\FE\Events;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event as Model;
use App\Models\Registration;
use App\Models\Competition;
use App\Models\Schemas\FE\Event as Schema;
use App\Models\Schemas\FE\WPResponse;
use App\Models\Requests\FERequest;

class Delete extends Controller
{
    public function index(Request $request)
    {
        $form = new FERequest($this);
        $form->validate($request);
        if ($request->get('path') != '/events/delete') {
            $this->authorize('not/ever');
        }

        $model = (object)$request->get('model');
        if (empty($model)) {
            $this->authorize('not/ever');
        }

        $data = $this->populateModel($model);
        $this->authorize('update', $data);
        $regs = Registration::where('registration_event', $data->getKey())->count();
        $comps = Competition::where('competition_event', $data->getKey())->count();
        if (($regs + $comps) == 0) {
            $data->delete();
            return response()->json(new WPResponse([]));
        }
        return response()->json(["success" => false, "data" => ["messages" => ["Cannot delete an event that is in use"]]]);
    }

    private function populateModel($model)
    {
        \Log::debug("populating based on " . $model->id);
        $data = Model::find($model->id);
        if (empty($data)) {
            $data = new Model();
        }
        return $data;
    }
}
