<?php

namespace App\Http\Controllers\FE\Fencers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accreditation;
use App\Models\Registration;
use App\Models\Country;
use App\Models\Fencer;
use App\Models\FencerLabel;
use App\Models\Result;
use App\Models\Schemas\FE\Fencer as FencerSchema;
use App\Models\Schemas\FE\WPResponse;
use App\Models\Requests\FERequest;
use App\Support\Services\FencerLabelService;
use DB;
use Auth;
use Carbon\Carbon;

class Delete extends Controller
{
    public function index(Request $request)
    {
        $form = new FERequest($this);
        $form->validate($request);
        if ($request->get('path') != '/fencers/delete') {
            $this->authorize('not/ever');
        }

        $model = (object)$request->get('model');
        if (empty($model)) {
            $this->authorize('not/ever');
        }

        $this->authorize('update', $fencer);
        $fencer = $this->populateFencer($model);
        $results = Result::where('result_fencer', $fencer->getKey())->count();
        if ($results == 0) {
            Accreditation::where('fencer_id', $fencer->getKey())->delete();
            Registration::where('registration_fencer', $fencer->getKey())->delete();
            $fencer->labels()->delete();
            if (file_exists($fencer->image())) {
                @unlink($fencer->image());
            }
            $fencer->delete();
            return response()->json(new WPResponse([]));
        }
        return response()->json(["success" => false]);
    }

    private function populateFencer($model)
    {
        $fencer = Fencer::find($model->id);
        if (empty($fencer)) {
            \Log::debug("creating new fencer for $model->id");
            $fencer = new Fencer();
        }
        return $fencer;
    }
}
