<?php

namespace App\Http\Controllers\FE\RoleTypes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RoleType;
use App\Models\Schemas\FE\RoleType as Schema;
use App\Models\Schemas\FE\WPResponse;
use App\Models\Requests\FERequest;

class Delete extends Controller
{
    public function index(Request $request)
    {
        $form = new FERequest($this);
        $form->validate($request);
        if ($request->get('path') != '/roletypes/delete') {
            $this->authorize('not/ever');
        }

        $model = (object)$request->get('model');
        if (empty($model)) {
            $this->authorize('not/ever');
        }

        $data = $this->populateModel($model);
        $this->authorize('update', $data);
        if (count($data->roles) == 0 && $data->exists) {
            $data->delete();
            return response()->json(new WPResponse([]));
        }
        return response()->json(["success" => false, "data" => ["messages" => ["Cannot delete a role-type that is in use"]]]);
    }

    private function populateModel($model)
    {
        $data = RoleType::find($model->id);
        if (empty($data)) {
            $data = new RoleType();
        }
        return $data;
    }
}
