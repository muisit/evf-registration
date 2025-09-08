<?php

namespace App\Http\Controllers\FE\Results;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Competition as Model;
use App\Models\Schemas\FE\WPResponse;
use App\Models\Requests\FERequest;
use App\Support\Services\RecalculateResultsService;
use Carbon\Carbon;

class Recalculate extends Controller
{
    public function index(Request $request, $competitionId)
    {
        $form = new FERequest($this);
        $form->validate($request);
        if ($request->get('path') != '/results/' . $competitionId . '/recalculate') {
            $this->authorize('not/ever');
        }

        $data = $this->populateModel($competitionId);
        if (empty($data)) {
            $this->authorize('not/ever');
        }

        $this->authorize('update', $data);

        $service = new RecalculateResultsService();
        $service->handle($data);

        return response()->json(new WPResponse([]));
    }

    private function populateModel($id)
    {
        return Model::find($id);
    }
}
