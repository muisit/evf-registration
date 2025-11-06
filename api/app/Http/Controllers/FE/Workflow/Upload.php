<?php

namespace App\Http\Controllers\FE\Workflow;

use App\Http\Controllers\Controller;
use App\Models\Workflow;
use App\Models\Schemas\FE\WPResponse;
use Illuminate\Http\Request;

class Upload extends Controller
{
    public function index(Request $request)
    {
        $id = intval($request->get('id'));
        $workflow = Workflow::find($id);
        if (empty($workflow)) {
            $workflow = new Workflow();
        }
        if (empty($request->file('picture'))) {
            $this->authorize('not/ever');
        }
        $this->authorize('update', $workflow);

        if ($request->hasFile('picture')) {
            $filename = hash_file("sha256", $request->file('picture')->path());
            $destination = storage_path('app/files') . '/' . $filename . '.dat';
            $request->file('picture')->move(storage_path('app/files'), $filename . '.dat');

            if (file_exists($destination)) {
                $workflow->addFile($destination, ["id" => $filename]);
                $workflow->save();
                \Log::debug("returning " . $workflow->getKey() . "/" . $filename);
                return response()->json(new WPResponse(["model" => ["id" => $workflow->getKey(), "file_id" => $filename]]));
            }
            return response()->json(["success" => false]);
        }
    }
}
