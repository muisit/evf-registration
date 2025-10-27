<?php

namespace App\Http\Controllers\FE\Events;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event as Model;
use App\Models\Schemas\Event as Schema;
use App\Models\Schemas\FE\WPResponse;
use App\Models\Requests\FERequest;
use Carbon\Carbon;

class Ranking extends Controller
{
    public function index(Request $request)
    {
        $form = new FERequest($this);
        $form->validate($request);
        if ($request->get('path') != '/events/ranking') {
            $this->authorize('not/ever');
        }

        $model = (object)$request->get('model');
        if (empty($model)) {
            $this->authorize('not/ever');
        }

        $data = $this->populateModel($model);
        if (empty($data)) {
            $this->authorize('not/ever');
        }
        $this->authorize('update', $data);
        if ($data->validate()) {
            $this->process($data, $model);
            return response()->json(new WPResponse(["item" => new Schema($data)]));
        }
        return response()->json(["success" => false]);
    }

    private function process(Model $data, $model)
    {
        $data->save();
        return $data;
    }

    private function populateModel($model)
    {
        $data = Model::find($model->id);
        if (empty($data)) {
            return null;
        }
        $data->event_in_ranking = (isset($model->inRanking) && $model->inRanking == 'Y') ? 'Y' : 'N';
        return $data;
    }
}
