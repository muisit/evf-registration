<?php

namespace App\Http\Controllers\FE\Registrars;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Registrar as Model;
use App\Models\WPUser;
use App\Models\Schemas\FE\Registrar as Schema;
use App\Models\Schemas\FE\WPResponse;
use App\Models\Requests\FERequest;
use Carbon\Carbon;

class Save extends Controller
{
    public function index(Request $request)
    {
        $form = new FERequest($this);
        $form->validate($request);
        if ($request->get('path') != '/registrars/save') {
            $this->authorize('not/ever');
        }

        $model = (object)$request->get('model');
        if (empty($model)) {
            $this->authorize('not/ever');
        }

        $data = $this->populateModel($model);
        $this->authorize('update', $data);
        if ($data->validate()) { // no rules, so always succeeds
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
        $country = Country::find($model->country_id);
        $data->country_id = empty($country) ? null : $country->getKey();

        $user = WPUser::find($model->user_id);
        $data->user_id = empty($user) ? null : $user->getKey();
        return $data;
    }
}
