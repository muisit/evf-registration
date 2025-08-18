<?php

namespace App\Http\Controllers\FE\Countries;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country as Model;
use App\Models\Schemas\FE\Country as Schema;
use App\Models\Schemas\FE\WPResponse;
use App\Models\Requests\FERequest;
use Carbon\Carbon;

class Save extends Controller
{
    public function index(Request $request)
    {
        $form = new FERequest($this);
        $form->validate($request);
        if ($request->get('path') != '/countries/save') {
            $this->authorize('not/ever');
        }

        $model = (object)$request->get('model');
        if (empty($model)) {
            $this->authorize('not/ever');
        }

        $data = $this->populateModel($model);
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
        $data->country_name = $model->name;
        $data->country_abbr = $model->abbr;
        $data->country_registered = $model->registered == 'Y' ? 'Y' : 'N';
        $data->country_flag_path = $model->flag ?? null;
        return $data;
    }
}
