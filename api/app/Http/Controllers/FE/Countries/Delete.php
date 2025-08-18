<?php

namespace App\Http\Controllers\FE\Countries;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country as Model;
use App\Models\Event;
use App\Models\Fencer;
use App\Models\Registration;
use App\Models\Schemas\FE\Country as Schema;
use App\Models\Schemas\FE\WPResponse;
use App\Models\Requests\FERequest;

class Delete extends Controller
{
    public function index(Request $request)
    {
        $form = new FERequest($this);
        $form->validate($request);
        if ($request->get('path') != '/countries/delete') {
            $this->authorize('not/ever');
        }

        $model = (object)$request->get('model');
        if (empty($model)) {
            $this->authorize('not/ever');
        }

        $data = $this->populateModel($model);
        $this->authorize('update', $data);

        if (Fencer::where('fencer_country', $data->getKey())->count() > 0) {
            return response()->json(["success" => false, "data" => ["messages" => ["Cannot delete a country that is in use"]]]);
        }
        if (Event::where('event_country', $data->getKey())->count() > 0) {
            return response()->json(["success" => false, "data" => ["messages" => ["Cannot delete a country that is in use"]]]);
        }
        $data->delete();
        return response()->json(new WPResponse([]));
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
