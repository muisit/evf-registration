<?php

namespace App\Http\Controllers\FE\Fencers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fencer;
use App\Models\Requests\FERequest;
use App\Models\Schemas\FE\Fencer as FencerSchema;
use Auth;
use Carbon\Carbon;

class View extends Controller
{
    /**
     * Return data for an individual fencer
     */
    public function index(Request $request)
    {
        $form = new FERequest($this);
        $form->validate($request);
        \Log::debug('form validated');
        if ($request->get('path') != '/fencers/view') {
            \Log::debug("path incorrect");
            $this->authorize('not/ever');
        }

        $model = (object)$request->get('model');
        if (empty($model)) {
            \Log::debug("model not set");
            $this->authorize('not/ever');
        }
        \Log::debug("model is " . json_encode($model));

        $fencer = Fencer::where('fencer_id', $model->id)->with('country')->first();
        if (empty($fencer)) {
            return response(404);
        }
        return response()->json(new FencerSchema($fencer, true));
    }
}
