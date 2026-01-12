<?php

namespace App\Http\Controllers\FE\Workflow;

use App\Http\Controllers\Controller;
use App\Models\Workflow;
use App\Models\Schemas\Workflow as Schema;
use App\Models\Schemas\FE\Response;
use App\Models\Requests\FERequest;
use Illuminate\Http\Request;
use App\Support\Services\WorkflowService;

class Step extends Controller
{
    public function index(Request $request)
    {
        $form = new FERequest($this);
        $form->validate($request);
        if ($request->get('path') != '/workflow/step') {
            $this->authorize('not/ever');
        }
        $model = (object)$request->get('model');
        if (empty($model)) {
            $this->authorize('not/ever');
        }

        $id = intval($model->id);
        $workflow = Workflow::find($id);
        if (empty($workflow)) {
            $workflow = new Workflow();
            $workflow->sandbox = ["name" => $model->name ?? 'generic'];
            $workflow->save();
        }
        $this->authorize('update', $workflow);

        try {
            $service = new WorkflowService($workflow);
            $workflow = $service->handle($model);
            if (!empty($workflow)) {
                return response()->json(new Response("ok", null, new Schema($workflow)));
            }
            else {
                return response()->json(new Response("ok", null, []));
            }
        }
        catch (e) {
        }
        return response()->json(new Response("error", "error processing workflow step"));
    }
}
