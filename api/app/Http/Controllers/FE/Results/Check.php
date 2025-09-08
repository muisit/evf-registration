<?php

namespace App\Http\Controllers\FE\Results;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Competition as Model;
use App\Models\Schemas\FE\WPResponse;
use App\Models\Requests\FERequest;
use App\Support\Services\ImportCheckService;
use Carbon\Carbon;

class Check extends Controller
{
    public function index(Request $request, $competitionId)
    {
        $form = new FERequest($this);
        $form->validate($request);
        if ($request->get('path') != '/results/' . $competitionId . '/check') {
            $this->authorize('not/ever');
        }

        $model = $request->get('model');
        if (empty($model) || !isset($model->ranking) || !isset($model->competition_id) || $model->competition_id != $competitionId) {
            $this->authorize('not/ever');
        }

        $data = $this->populateModel($competitionId);
        if (empty($data)) {
            $this->authorize('not/ever');
        }

        $this->authorize('update', $data);

        $service = new ImportCheckService();
        $service->handle($data, $model->ranking);

        return response()->json(new WPResponse([]));
    }

    private function populateModel($id)
    {
        return Model::find($id);
    }
}
