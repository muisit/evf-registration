<?php

namespace App\Http\Controllers\FE\Fencers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Fencer;
use App\Models\FencerLabel;
use App\Models\Schemas\FE\Fencer as FencerSchema;
use App\Models\Schemas\FE\WPResponse;
use App\Models\Requests\FERequest;
use DB;
use Auth;
use Carbon\Carbon;

class Presave extends Controller
{
    /**
     * Pass some pre-save checks
     */
    public function index(Request $request)
    {
        $form = new FERequest($this);
        $form->validate($request);
        if ($request->get('path') != '/fencers/presavecheck') {
            $this->authorize('not/ever');
        }

        $model = (object)$request->get('model');
        if (empty($model)) {
            $this->authorize('not/ever');
        }

        $fencer = $this->populateFencer($model);
        $this->authorize('update', $fencer);
        $suggestions = $this->check($fencer);

        return response()->json(new WPResponse(["suggestions" => $suggestions]));
    }

    private function check(Fencer $fencer)
    {
        // this check is meant to allow the front-end to check if a given fencer may have,
        // per-chance, a duplicate in the database. If so, we can signal the user that he
        // may need to request a change-of-country
        // We only do this for new entries, not for existing ones.
        if (!$fencer->exists) {
            $qry = Fencer::where("fencer_dob", new Carbon($fencer->fencer_dob))
                ->join(FencerLabel::tableName() . ' AS ln', function ($on) {
                    return $on->on('ln.fencer_id', '=', Fencer::tableName() . '.fencer_id')
                        ->where('ln.type', '=', 'last');
                })
                ->join(FencerLabel::tableName() . ' AS fn', function ($on) {
                    return $on->on('fn.fencer_id', '=', Fencer::tableName() . '.fencer_id')
                        ->where('fn.type', '=', 'first');
                })
                ->where(DB::Raw("SOUNDEX('" . addslashes($fencer->fencer_firstname) . "')"), DB::Raw("SOUNDEX(fn.label)"))
                ->where(DB::Raw("SOUNDEX('" . addslashes($fencer->fencer_surname) . "')"), DB::Raw("SOUNDEX(ln.label)"))
                ->where(Fencer::tableName() . '.fencer_id', '<>', $fencer->getKey());
            $qry = $qry->join(Country::tableName() . ' AS c', 'c.country_id', '=', Fencer::tableName() . '.fencer_country')
                ->select(Fencer::tableName() . ".*", "c.country_name");

            $results = $qry->get()->mapWithKeys(fn ($item) => [$item->getKey() => new FencerSchema($item, false)])->toArray();
            return array_values($results);
        }
        return [];
    }

    private function populateFencer($model)
    {
        $fencer = Fencer::find($model->id);
        if (empty($fencer)) {
            $fencer = new Fencer();
        }
        $fencer->fencer_firstname = $model->firstname;
        $fencer->fencer_surname = $model->name;
        $fencer->fencer_country = $model->country_id;
        $fencer->fencer_gender = $model->gender;
        $fencer->fencer_picture = $model?->picture ?? 'N';
        $fencer->fencer_dob = $model->birthday;
        return $fencer;
    }
}
