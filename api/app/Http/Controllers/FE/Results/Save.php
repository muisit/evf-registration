<?php

namespace App\Http\Controllers\FE\Results;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Result as Model;
use App\Models\Schemas\FE\Result as Schema;
use App\Models\Schemas\FE\WPResponse;
use App\Models\Requests\FERequest;
use Carbon\Carbon;

class Save extends Controller
{
    public function index(Request $request, $competitionId)
    {
        $form = new FERequest($this);
        $form->validate($request);
        if ($request->get('path') != '/results/' . $competitionId . '/save') {
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
            return response()->json(new WPResponse(["item" => new Schema($data)]));
        }
        return response()->json(["success" => false]);
    }

    private function process(Model $data)
    {
        $data->save();
        return $data;
    }

    private function populateModel($model)
    {
        $data = Model::find($model->id);
        if (empty($data)) {
            $data = new Model();
        }
        $data->result_place = intval($model->place);
        $data->result_entry = intval($model->entry);
        $data->result_points = floatval($model->points);
        $data->result_de_points = floatval($model->de_points);
        $data->result_podium_points = floatval($model->podium_points);
        $data->result_total_points = $data->result_points + $data->result_de_points + $data->result_podium_points;
        $data->result_in_ranking = $model->ranked;
        return $data;
    }
}
