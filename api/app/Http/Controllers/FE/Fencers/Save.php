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
use App\Support\Services\FencerLabelService;
use DB;
use Auth;
use Carbon\Carbon;

class Save extends Controller
{
    public function index(Request $request)
    {
        $form = new FERequest($this);
        $form->validate($request);
        if ($request->get('path') != '/fencers/save') {
            $this->authorize('not/ever');
        }

        $model = (object)$request->get('model');
        if (empty($model)) {
            $this->authorize('not/ever');
        }

        $fencer = $this->populateFencer($model);
        $this->authorize('update', $fencer);
        if ($fencer->validate()) {
            $this->process($fencer);
            return response()->json(new WPResponse(["item" => new FencerSchema($fencer)]));
        }
        return response()->json(["success" => false]);
    }

    private function process(Fencer $fencer)
    {
        $service = new FencerLabelService();
        $oldData = $fencer;
        if ($fencer->exists) {
            $oldData = Fencer::find($fencer->getKey())->with('labels')->first();
        }
        $fencer->save();
        $service->updateFencer($oldData, $fencer->fencer_firstname, $fencer->fencer_surname);
        return $fencer;
    }

    private function populateFencer($model)
    {
        $fencer = Fencer::find($model->id);
        if (empty($fencer)) {
            $fencer = new Fencer();
        }
        $fencer->fencer_firstname = $model->firstname;
        $fencer->fencer_surname = mb_strtoupper($model->name, 'UTF-8');
        $fencer->fencer_country = $model->country_id;
        $fencer->fencer_gender = $model->gender;
        $fencer->fencer_picture = $model?->picture ?? 'N';
        $fencer->fencer_dob = $model->birthday;
        return $fencer;
    }
}
