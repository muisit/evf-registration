<?php

namespace App\Http\Controllers\FE\Roles;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role as Model;
use App\Models\RoleType;
use App\Models\Requests\FERequest;
use App\Models\Schemas\FE\Role as Schema;
use App\Models\Schemas\FE\WPResponse;

class Index extends Controller
{
    /**
     * List using sorting and filtering
     */
    public function index(Request $request)
    {
        $form = new FERequest($this);
        $form->validate($request);
        if ($request->get('path') != '/roles') {
            $this->authorize('not/ever');
        }

        $model = (object)$request->get('model');
        if (empty($model)) {
            $this->authorize('not/ever');
        }

        $this->authorize('viewAny', Model::class);
        $limit = $model->pagesize ?? 20;
        $offset = $model->offset ?? 0;
        $qry = $this->filterQuery($model);
        $qry = $this->sortQuery($qry, $model);

        $total = $qry->count();
        $data = $qry->limit($limit)->offset($offset)->get()->map(fn ($item) => new Schema($item, false));
        return response()->json(new WPResponse(["list" => $data, "total" => $total]));
    }

    private function sortQuery($qry, $model)
    {
        $sort = $model->sort ?? 'i';

        for ($i = 0; $i < strlen($sort); $i++) {
            $c = $sort[$i];
            switch ($c) {
                default:
                case 'i': $qry->orderBy('role_id', 'asc'); break;
                case 'I': $qry->orderBy('role_id', 'desc'); break;
                case 'n': $qry->orderBy('role_name', 'asc'); break;
                case 'N': $qry->orderBy('role_name', 'desc'); break;
            }
        }
        return $qry;
    }

    private function filterQuery($model)
    {
        $qry = null;
        $filter = $model->filter?->name ?? ($model->filter ?? '');
        if (strlen($filter)) {
            $name = $filter; // str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $filter->name);
            $qry = Model::where('role_name', 'like', $name . '%');
        }
        else {
            $qry = Model::where('role_id', '>', 0);
        }
        return $qry;
    }
}
