<?php

namespace App\Http\Controllers\FE\Results;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Category;
use App\Models\Competition;
use App\Models\Country;
use App\Models\Fencer;
use App\Models\Result as Model;
use App\Models\Weapon;
use App\Models\Requests\FERequest;
use App\Models\Schemas\FE\Result as Schema;
use App\Models\Schemas\FE\WPResponse;
use DB;

class Index extends Controller
{
    /**
     * List using sorting and filtering
     */
    public function index(Request $request, $competitionId)
    {
        $form = new FERequest($this);
        $form->validate($request);
        if ($request->get('path') != '/results/' . $competitionId) {
            $this->authorize('not/ever');
        }

        $model = (object)$request->get('model');
        if (empty($model)) {
            $this->authorize('not/ever');
        }

        $this->authorize('viewAny', Model::class);
        $filter = $model->filter;
        $obj = null;
        if ($filter && isset($filter["fencer"]) && $filter["fencer"] === true) {
            $obj = Fencer::find($competitionId);
        }
        else {
            $obj = Competition::find($competitionId);
        }

        if (empty($obj)) {
            $this->authorize('not/ever');
        }
        $this->authorize('view', $obj);

        $limit = $model->pagesize ?? 20;
        $offset = $model->offset ?? 0;
        $qry = $this->filterQuery($model, $obj);
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
                case 'p': $qry->orderBy("result_place", "asc"); break;
                case 'P': $qry->orderBy("result_place", "desc"); break;
                case 't': $qry->orderBy("result_total_points", "asc"); break;
                case 'T': $qry->orderBy("result_total_points", "desc"); break;
                case 's': $qry->orderBy("result_points", "asc"); break;
                case 'S': $qry->orderBy("result_points", "desc"); break;
                case 'i': $qry->orderBy("result_id", "asc"); break;
                case 'I': $qry->orderBy("result_id", "desc"); break;
                case 'b': $qry->orderBy("f.fencer_dob", "asc"); break;
                case 'B': $qry->orderBy("f.fencer_dob", "desc"); break;
                case 'n': $qry->orderBy("f.fencer_surname", "asc"); break;
                case 'N': $qry->orderBy("f.fencer_surname", "desc"); break;
                case 'f': $qry->orderBy("f.fencer_firstname", "asc"); break;
                case 'F': $qry->orderBy("f.fencer_firstname", "desc"); break;
                case 'c': $qry->orderBy("c.country_name", "asc"); break;
                case 'C': $qry->orderBy("c.country_name", "desc"); break;
                case 'e': $qry->orderBy("e.event_year", "asc")->orderBy("e.event_open", "asc"); break;
                case 'E': $qry->orderBy("e.event_year", "desc")->orderBy("e.event_open", "desc"); break;
            }
        }
        return $qry;
    }

    private function filterQuery($model, $obj)
    {
        // obj can be fencer or competition, which have a polymorphic results() relation
        $qry = $obj->results()
            // ad joins needed for sorting
            ->leftJoin(Competition::tableName() . " as cm", "cm.competition_id", "=", "result_competition")
            ->leftJoin(Event::tableName() . " as e", "e.event_id", "=", "cm.competition_event")
            ->leftJoin(Fencer::tableName() . " as f", "f.fencer_id", "=", "result_fencer")
            ->leftJoin(Country::tableName() . " as c", "c.country_id", "=", "fencer_country")
            // select the sortable fields
            ->select(Model::tableName() . ".*", "e.*", "f.*", "c.*")
            // add the relations to allow the schema to fill without additional subqueries
            ->with('competition', 'competition.event', 'competition.weapon', 'competition.category', 'fencer', 'fencer.country');
        return $qry;
    }
}
