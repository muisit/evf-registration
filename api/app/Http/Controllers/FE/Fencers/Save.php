<?php

namespace App\Http\Controllers\FE\Fencers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Schemas\Fencer as FencerSchema;
use App\Models\Requests\Fencer as FencerRequest;
use Auth;
use Carbon\Carbon;

class Save extends Controller
{
    /**
     * Save WP front-end fencer data to the database
     */
    public function index(Request $request)
    {
        $form = new FencerRequest($this);
        $model = $form->validate($request);
        if (!empty($model) && $model !== false) {
            return response()->json(new FencerSchema($model));
        }
        return response()->json([]);
    }
}
