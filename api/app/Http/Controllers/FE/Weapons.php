<?php

namespace App\Http\Controllers\FE;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Weapon as Model;
use App\Models\Requests\FERequest;
use App\Models\Schemas\Weapon as Schema;
use App\Models\Schemas\FE\WPResponse;

class Weapons extends Controller
{
    /**
     * List using sorting and filtering
     */
    public function index(Request $request)
    {
        if ($request->get('path') != '/weapons') {
            $this->authorize('not/ever');
        }

        $this->authorize('viewAny', Model::class);

        $qry = Model::where('weapon_id', '>', 0)->orderBy('weapon_name', 'asc');
        $total = $qry->count();
        $data = $qry->get()->map(fn ($i) => new Schema($i))->toArray();
        return response()->json(new WPResponse(["list" => $data, "total" => $total]));
    }
}
