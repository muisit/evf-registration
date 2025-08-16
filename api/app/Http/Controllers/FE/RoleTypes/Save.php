<?php

namespace App\Http\Controllers\FE\RoleTypes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RoleType;
use App\Models\Schemas\FE\RoleType as Schema;
use App\Models\Schemas\FE\WPResponse;
use App\Models\Requests\FERequest;
use Carbon\Carbon;

class Save extends Controller
{
    public function index(Request $request)
    {
        $form = new FERequest($this);
        $form->validate($request);
        if ($request->get('path') != '/roletypes/save') {
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

    private function process(RoleType $data)
    {
        $data->save();
        return $data;
    }

    private function populateModel($model)
    {
        $data = RoleType::find($model->id);
        if (empty($data)) {
            $data = new RoleType();
        }
        $data->role_type_name = $model->name;
        $data->org_declaration = $model->org_declaration;
        return $data;
    }
}
