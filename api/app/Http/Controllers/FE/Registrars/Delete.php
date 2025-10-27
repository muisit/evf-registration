<?php

namespace App\Http\Controllers\FE\Registrars;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Registrar as Model;
use App\Models\Schemas\FE\Registrar as Schema;
use App\Models\Schemas\FE\WPResponse;
use App\Models\Requests\FERequest;

class Delete extends Controller
{
    public function index(Request $request)
    {
        $form = new FERequest($this);
        $form->validate($request);
        if ($request->get('path') != '/registrars/delete') {
            $this->authorize('not/ever');
        }

        $model = (object)$request->get('model');
        if (empty($model)) {
            $this->authorize('not/ever');
        }

        $data = $this->populateModel($model);
        $this->authorize('update', $data);
        // always allowed, there are no dependencies
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
