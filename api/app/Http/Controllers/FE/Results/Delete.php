<?php

namespace App\Http\Controllers\FE\Results;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Result as Model;
use App\Models\Schemas\FE\Result as Schema;
use App\Models\Schemas\FE\Response;
use App\Models\Requests\FERequest;
use Carbon\Carbon;

class Delete extends Controller
{
    public function index(Request $request, $competitionId)
    {
        $form = new FERequest($this);
        $form->validate($request);
        if ($request->get('path') != '/results/' . $competitionId . '/delete') {
            $this->authorize('not/ever');
        }

        $model = (object)$request->get('model');
        if (empty($model)) {
            $this->authorize('not/ever');
        }

        $data = $this->populateModel($model);
        if ($data->result_competition != intval($competitionId)) {
            $this->authorize('not/ever');
        }

        $this->authorize('update', $data);
        if ($data->validate()) {
            $this->process($data);
            return response()->json(new Response("ok"));
        }
        return response()->json(new Response('error', "could not find result"));
    }

    private function process(Model $data)
    {
        $data->delete();
        return $data;
    }

    private function populateModel($model)
    {
        $data = Model::find($model->id);
        if (empty($data)) {
            $data = new Model();
        }
        return $data;
    }
}
