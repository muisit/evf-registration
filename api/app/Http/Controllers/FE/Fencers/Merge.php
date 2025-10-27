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

class Merge extends Controller
{
    public function index(Request $request)
    {
        $form = new FERequest($this);
        $form->validate($request);
        if ($request->get('path') != '/fencers/merge') {
            $this->authorize('not/ever');
        }

        $model = (object)$request->get('model');
        if (empty($model)) {
            $this->authorize('not/ever');
        }

        $this->authorize('update', $fencer);
        $fencer1 = Fencer::find($model->id1);
        $fencer2 = Fencer::find($model->id2);

        if (!$this->process($fencer1, $fencer2)) {
            return response()->json(["success" => false]);
        }
        return response()->json(new WPResponse([]));
    }

    private function process(Fencer $fencer1, Fencer $fencer2)
    {
        if (empty($fencer1) || empty($fencer2)) {
            return false;
        }

        if ($fencer1->getKey() == $fencer2->getKey()) {
            return false;
        }

        // this is the one place we retain the link to Accreditation and Registration
        Accreditation::where('fencer_id', $fencer2->getKey())->update([
            "fencer_id" => $fencer1->getKey(),
            "is_dirty" => Carbon::now(),
        ]);
        Registration::where('registration_fencer', $fencer2->getKey())->update([
            "registration_fencer" => $fencer1->getKey()
        ]);
        Result::where('result_fencer', $fencer2->getKey())->update([
            "result_fencer" => $fencer1->getKey()
        ]);

        if (file_exists($fencer1->image())) {
            if (file_exists($fencer2->image())) {
                if (intval($fencer1->getKey()) < intval($fencer2->getKey())) {
                    // keep the one linked to the newest entry
                    @rename($fencer2->image(), $fencer1->image());
                    $fencer1->fencer_picture = $fencer2->fencer_picture;
                    $fencer1->save();
                }
                else {
                    @unlink($fencer2->image());
                }
            }
            // else don't do anything, the file for fencer1 is kept, for fencer2 does not exist
        }
        else if(file_exists($fencer2->image())) {
            @rename($fencer2->image(), $fencer1->image());
            $fencer1->fencer_picture = $fencer2->fencer_picture;
            $fencer2->save();
        }

        // merge the labels
        FencerLabel::where('fencer_id', $fencer2->getKey())->update(['fencer_id' => $fencer1->getKey()]);
        // remove duplicates
        $found = ["last" => [], "first" => []];
        foreach ($fencer1->labels()->get() as $label) {
            if (!isset($found[$label->type][mb_strtoupper($label->label, 'UTF-8')])) {
                $found[$label->type][mb_strtoupper($label->label, 'UTF-8')] = $label;
            }
            else {
                $label->delete(); // remove the duplicate
            }
        }

        $fencer2->delete();
        return true;
    }
}
