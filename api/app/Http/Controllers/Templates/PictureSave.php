<?php

namespace App\Http\Controllers\Templates;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccreditationTemplate;
use App\Models\Schemas\ReturnStatus;
use App\Models\Schemas\AccreditationTemplatePicture;

class PictureSave extends Controller
{
    /**
     * Store template associated picture to storage
     *
     * @OA\Post(
     *     path = "/templates/{templateId}/picture",
     *     @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="application/octet-stream",
     *       @OA\Schema(
     *         required={"content"},
     *         @OA\Property(
     *           description="Binary content of file",
     *           property="content",
     *           type="string",
     *           format="binary"
     *         )
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *       response = "200",
     *       description = "Successful store",
     *       @OA\JsonContent(ref="#/components/schemas/AccreditationTemplatePicture")
     *   ),
     *   @OA\Response(
     *       response = "404",
     *       description = "Failure to store",
     *       @OA\JsonContent(ref="#/components/schemas/ReturnSchema")
     *   ),
     * )
     */
    public function index(Request $request, string $templateId)
    {
        $template = AccreditationTemplate::where('id', $templateId)->first();
        if (empty($template) || empty($request->file('picture'))) {
            $this->authorize('not/ever');
        }
        $this->authorize('update', $template);

        if ($request->hasFile('picture')) {
            $pictureId = uniqid();
            $imageLocation = $template->image($pictureId, $request->file('picture')->getClientOriginalExtension());
            $mimeType = $request->file('picture')->getMimeType();
            $request->file('picture')->move(dirname($imageLocation), basename($imageLocation));
            if (file_exists($imageLocation)) {
                $size = getimagesize($imageLocation);
                $picture = (object) [
                    "file_id" => $pictureId,
                    "file_ext" => $request->file('picture')->getClientOriginalExtension(),
                    "file_name" => $request->file('picture')->getClientOriginalName(),
                    "file_mimetype" => $mimeType,
                    "width" => $size[0],
                    "height" => $size[1]
                ];
                return response()->json(new AccreditationTemplatePicture($picture));
            }
        }
        return response()->json(new ReturnStatus('error', 'missing image data'), 404);
    }
}
