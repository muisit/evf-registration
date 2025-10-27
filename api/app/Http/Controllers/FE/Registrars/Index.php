<?php

namespace App\Http\Controllers\FE\Registrars;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Registrar as Model;
use App\Models\WPUser;
use App\Models\Requests\FERequest;
use App\Models\Schemas\FE\Registrar as Schema;
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
        if ($request->get('path') != '/registrars') {
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
                case 'i': $qry->orderBy(Model::tableName() . '.id', 'asc'); break;
                case 'I': $qry->orderBy(Model::tableName() . '.id', 'desc'); break;
                case 'n': $qry->orderBy('user_nicename', 'asc'); break;
                case 'N': $qry->orderBy('user_nicename', 'desc'); break;
                case 'c': $qry->orderBy('country_name', 'asc'); break;
                case 'C': $qry->orderBy('country_name', 'desc'); break;
            }
        }
        return $qry;
    }

    private function filterQuery($model)
    {
        return Model::where(Model::tableName() . '.id', '>', 0)
            ->leftJoin(WPUser::tableName() . " as user", "user.ID", "=", Model::tableName() . ".user_id")
            ->leftJoin(Country::tableName() . " as c", "c.country_id", "=", Model::tableName() . ".country_id")
            ->select(Model::tableName() . ".*", "user.user_nicename", "c.country_name");
    }
}
