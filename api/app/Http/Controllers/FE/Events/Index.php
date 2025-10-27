<?php

namespace App\Http\Controllers\FE\Events;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event as Model;
use App\Models\Competition;
use App\Models\Result;
use App\Models\Schemas\Event as Schema;
use App\Models\Schemas\FE\WPResponse;
use DB;

class Index extends Controller
{
    /**
     * List using sorting and filtering
     */
    public function index(Request $request)
    {
        if ($request->get('path') != '/events') {
            $this->authorize('not/ever');
        }

        $model = (object)$request->get('model');
        if (empty($model)) {
            $this->authorize('not/ever');
        }

        $limit = $model->pagesize ?? 20;
        if ($limit == 0) $limit = 100000;
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
                case 'i': $qry->orderBy('event_id', 'asc'); break;
                case 'I': $qry->orderBy('event_id', 'desc'); break;
                case 'n': $qry->orderBy('event_name', 'asc'); break;
                case 'N': $qry->orderBy('event_name', 'desc'); break;
                case 'd': $qry->orderBy('event_open', 'asc'); break;
                case 'D': $qry->orderBy('event_open', 'desc'); break;
                case 'y': $qry->orderBy('event_year', 'asc'); break;
                case 'Y': $qry->orderBy('event_year', 'desc'); break;
                case 't': $qry->orderBy('event_type', 'asc'); break;
                case 'T': $qry->orderBy('event_type', 'desc'); break;
                case 'r': $qry->orderBy('event_in_ranking', 'asc'); break;
                case 'R': $qry->orderBy('event_in_ranking', 'desc'); break;
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
            $qry = Model::where(function ($qry) use ($name) {
                return $qry->where('event_name', 'like', $name . '%')->orWhere('event_location', 'like', $name . '%');
            });
        }
        else {
            $qry = Model::where('event_id', '>', 0);
        }

        if (isset($model->special) && strlen($model->special)) {
            if ($model->special == "with_competitions") {
                $qry->whereExists(function ($query) {
                    $query->select(DB::Raw("*"))->from(Competition::tableName())->whereColumn("competition_event", "event_id");
                });
            }
            if ($model->special == "with_results") {
                $qry->whereExists(function ($query) {
                    $query->select(DB::Raw("*"))->from(Competition::tableName())
                    ->join(Result::tableName() . " as r", "result_competition", "=", "competition_id")
                    ->whereColumn("competition_event", "event_id");
                });
                $qry->whereRaw("exists(select * from " . Competition::tableName() . " c, " . Result::tableName() . " r where c.competition_event=" . Model::tableName() . ".event_id and r.result_competition=c.competition_id)");
            }
        }

        return $qry;
    }
}
