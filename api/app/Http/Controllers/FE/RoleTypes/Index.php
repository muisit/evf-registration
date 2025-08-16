<?php

namespace App\Http\Controllers\FE\RoleTypes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RoleType;
use App\Models\Requests\FERequest;
use App\Models\Schemas\FE\RoleType as RoleTypeSchema;
use App\Models\Schemas\FE\WPResponse;
use Auth;
use Carbon\Carbon;

class Index extends Controller
{
    /**
     * List using sorting and filtering
     */
    public function index(Request $request)
    {
        $form = new FERequest($this);
        $form->validate($request);
        if ($request->get('path') != '/roletypes') {
            $this->authorize('not/ever');
        }

        $model = (object)$request->get('model');
        if (empty($model)) {
            $this->authorize('not/ever');
        }

        $this->authorize('viewAny', RoleType::class);
        $limit = $model->pagesize ?? 20;
        $offset = $model->offset ?? 0;
        $qry = $this->filterQuery($model);
        $qry = $this->sortQuery($qry, $model);

        $total = $qry->count();
        $data = $qry->limit($limit)->offset($offset)->get()->map(fn ($item) => new RoleTypeSchema($item, false));
        return response()->json(new WPResponse(["list" => $data, "total" => $total]));
    }

    private function sortQuery($qry, $model)
    {
        $sort = $model->sort ?? 'i';

        for ($i = 0; $i < strlen($sort); $i++) {
            $c = $sort[$i];
            switch ($c) {
                default:
                case 'i': $qry->orderBy('role_type_id', 'asc'); break;
                case 'I': $qry->orderBy('role_type_id', 'desc'); break;
                case 'n': $qry->orderBy('role_type_name', 'asc'); break;
                case 'N': $qry->orderBy('role_type_name', 'desc'); break;
            }
        }
        return $qry;
    }

    private function filterQuery($model)
    {
        $qry = null;
        $filter = $model->filter ?? '';
        if (strlen($filter)) {
            $name = $filter; // str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $filter->name);
            $qry = RoleType::where('role_type_name', 'like', $name . '%');
        }
        else {
            $qry = RoleType::where('role_type_id', '>', 0);
        }
        return $qry;
    }
}
