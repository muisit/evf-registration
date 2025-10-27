<?php

namespace App\Http\Controllers\FE;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category as Model;
use App\Models\Requests\FERequest;
use App\Models\Schemas\Category as Schema;
use App\Models\Schemas\FE\WPResponse;

class Categories extends Controller
{
    /**
     * List using sorting and filtering
     */
    public function index(Request $request)
    {
        if ($request->get('path') != '/categories') {
            $this->authorize('not/ever');
        }

        $this->authorize('viewAny', Model::class);

        $qry = Model::where('category_id', '>', 0)->orderBy('category_name', 'asc');
        $total = $qry->count();
        $data = $qry->get()->map(fn ($i) => new Schema($i))->toArray();
        return response()->json(new WPResponse(["list" => $data, "total" => $total]));
    }
}
