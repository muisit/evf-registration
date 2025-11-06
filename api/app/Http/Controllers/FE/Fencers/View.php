<?php

namespace App\Http\Controllers\FE\Fencers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fencer;
use App\Models\Requests\FERequest;
use App\Models\Schemas\FE\WPResponse;
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
        if ($request->get('path') != '/fencers/view') {
            $this->authorize('not/ever');
        }

        $model = (object)$request->get('model');
        if (empty($model)) {
            $this->authorize('not/ever');
        }

        $fencer = Fencer::where('fencer_id', $model->id)->with('country')->first();
        $this->authorize('view', $fencer);
        if (empty($fencer)) {
            return response(404);
        }
        return response()->json(new WPResponse(["item" => new FencerSchema($fencer, true)]));
    }
}
