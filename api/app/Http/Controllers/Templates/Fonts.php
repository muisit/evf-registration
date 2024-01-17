<?php

namespace App\Http\Controllers\Templates;

use App\Models\AccreditationTemplate;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Support\Services\PDF\FontManager;

class Fonts extends Controller
{
    /**
     * List of available fonts for the editor
     *
     * @OA\Get(
     *     path = "/templates/fonts",
     *     @OA\Response(
     *         response = "200",
     *         description = "List of available fonts",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(type="string")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', AccreditationTemplate::class);
        return response()->json(FontManager::PDF_FONTS);
    }
}
