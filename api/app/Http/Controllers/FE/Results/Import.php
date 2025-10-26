<?php

namespace App\Http\Controllers\FE\Results;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Competition as Model;
use App\Models\Schemas\FE\WPResponse;
use App\Models\Requests\FERequest;
use App\Support\Services\ImportService;
use Carbon\Carbon;

class Import extends Controller
{
    public function index(Request $request, $competitionId)
    {
        \Log::debug("import endpoint for results");
        $form = new FERequest($this);
        $form->validate($request);
        if ($request->get('path') != '/results/' . $competitionId . '/import') {
            $this->authorize('not/ever');
        }

        $model = (object)$request->get('model');
        if (empty($model) || !isset($model->import) || !isset($model->competition_id) || $model->competition_id != intval($competitionId)) {
            \Log::debug("no import or wrong competition " . json_encode($model, JSON_PRETTY_PRINT));
            $this->authorize('not/ever');
        }

        $data = $this->populateModel(intval($competitionId));
        if (empty($data)) {
            \Log::debug("no competition found");
            $this->authorize('not/ever');
        }

        $this->authorize('update', $data);

        $service = new ImportService();
        \Log::debug("calling handle on service for " . json_encode($data));
        $result = $service->handle($data, $model->import);

        return response()->json(new WPResponse($result));
    }

    private function populateModel($id)
    {
        return Model::find($id);
    }
}
