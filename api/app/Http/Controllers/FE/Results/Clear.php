<?php

namespace App\Http\Controllers\FE\Results;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Competition as Model;
use App\Models\Schemas\FE\Result as Schema;
use App\Models\Schemas\FE\WPResponse;
use App\Models\Requests\FERequest;
use Carbon\Carbon;

class Clear extends Controller
{
    public function index(Request $request, $competitionId)
    {
        $form = new FERequest($this);
        $form->validate($request);
        if ($request->get('path') != '/results/' . $competitionId . '/clear') {
            $this->authorize('not/ever');
        }

        $data = $this->populateModel($competitionId);
        if (empty($data)) {
            $this->authorize('not/ever');
        }

        $this->authorize('update', $data);
        $data->results()->delete();
        return response()->json(new WPResponse([]));
    }

    private function populateModel($id)
    {
        return Model::find($id);
    }
}
