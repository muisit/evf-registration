<?php

namespace App\Http\Controllers\FE\Fencers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Fencer;
use App\Models\Requests\FERequest;
use App\Models\Schemas\FE\Fencer as FencerSchema;
use App\Models\Schemas\FE\WPResponse;
use Auth;
use Carbon\Carbon;

class INdex extends Controller
{
    /**
     * List fencers using sorting and filtering
     */
    public function index(Request $request)
    {
        $form = new FERequest($this);
        $form->validate($request);
        if ($request->get('path') != '/fencers') {
            $this->authorize('not/ever');
        }

        $model = (object)$request->get('model');
        if (empty($model)) {
            $this->authorize('not/ever');
        }

        $this->authorize('viewAny', Fencer::class);
        $limit = $model->pagesize ?? 20;
        $offset = $model->offset ?? 0;
        $qry = $this->filterQuery($model);
        $qry = $this->sortQuery($qry, $model);

        $qry = $qry->join(Country::tableName() . ' AS c', 'c.country_id', '=', Fencer::tableName() . '.fencer_country')
            ->select(Fencer::tableName() . ".*", "c.country_name");

        $total = $qry->count();
        $fencers = $qry->limit($limit)->offset($offset)->get()->map(fn ($item) => new FencerSchema($item, false));
        return response()->json(new WPResponse(["list" => $fencers, "total" => $total]));
    }

    private function sortQuery($qry, $model)
    {
        $sort = $model->sort ?? 'i';

        for ($i = 0; $i < strlen($sort); $i++) {
            $c = $sort[$i];
            switch ($c) {
                default:
                case 'i': $qry->orderBy('fencer_id', 'asc'); break;
                case 'I': $qry->orderBy('fencer_id', 'desc'); break;
                case 'n': $qry->orderBy('fencer_surname', 'asc'); break;
                case 'N': $qry->orderBy('fencer_surname', 'desc'); break;
                case 'f': $qry->orderBy('fencer_firstname', 'asc'); break;
                case 'F': $qry->orderBy('fencer_firstname', 'desc'); break;
                case 'c': $qry->orderBy('country_name', 'asc'); break;
                case 'C': $qry->orderBy('country_name', 'desc'); break;
                case 'g': $qry->orderBy('fencer_gender', 'asc'); break;
                case 'G': $qry->orderBy('fencer_gender', 'desc'); break;
                case 'b': $qry->orderBy('fencer_dob', 'asc'); break;
                case 'B': $qry->orderBy('fencer_dob', 'desc'); break;
            }
        }
        return $qry;
    }

    private function filterQuery($model)
    {
        $qry = null;
        $filter = (object)($model->filter ?? []);
        if (isset($filter?->name) && strlen($filter->name)) {
            $name = $filter->name; // str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $filter->name);
            $qry = Fencer::where('fencer_surname', 'like', $name . '%');

            if (isset($filter->country)) {
                $qry = $qry->where('fencer_country', intval($filter->country));
            }
        }
        else if(isset($filter?->country)) {
            $qry = Fencer::where('fencer_country', intval($filter->country));
        }
        else {
            $qry = Fencer::where('fencer_id', '>', 0);
        }
        return $qry;
    }
}
