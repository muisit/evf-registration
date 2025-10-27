<?php

namespace App\Http\Controllers\FE\Roles;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role as Model;
use App\Models\Registration;
use App\Models\Schemas\FE\Role as Schema;
use App\Models\Schemas\FE\WPResponse;
use App\Models\Requests\FERequest;

class Delete extends Controller
{
    public function index(Request $request)
    {
        $form = new FERequest($this);
        $form->validate($request);
        if ($request->get('path') != '/roles/delete') {
            $this->authorize('not/ever');
        }

        $model = (object)$request->get('model');
        if (empty($model)) {
            $this->authorize('not/ever');
        }

        $data = $this->populateModel($model);
        $this->authorize('update', $data);
        if (Registration::where('registration_role', $data->getKey())->count() == 0) {
            $data->delete();
            return response()->json(new WPResponse([]));
        }
        return response()->json(["success" => false, "data" => ["messages" => ["Cannot delete a role that is in use"]]]);
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
