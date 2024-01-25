<?php

namespace App\Http\Controllers\Templates;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccreditationTemplate;
use App\Models\Schemas\ReturnStatus;
use App\Models\Schemas\AccreditationTemplatePicture;

class PictureDelete extends Controller
{
    /**
     * Remove template associated picture from storage
     *
     * @OA\Post(
     *     path = "/templates/{templateId}/picture/{id}/remove",
     *   @OA\Response(
     *       @OA\JsonContent(ref="#/components/schemas/ReturnSchema")
     *   ),
     * )
     */
    public function index(Request $request, string $templateId, string $pictureId)
    {
        $template = AccreditationTemplate::where('id', $templateId)->first();
        if (empty($template)) {
            $this->authorize('not/ever');
        }
        $this->authorize('update', $template);

        $content = json_decode($template->content);
        if (isset($content->pictures)) {
            foreach ($content->pictures as $picture) {
                if ($picture->file_id == $pictureId) {
                    $imageLocation = $template->image($pictureId, $picture->file_ext);
                    if (file_exists($imageLocation)) {
                        @unlink($imageLocation);
                    }
                    return response()->json(new ReturnStatus('ok'));
                }
            }
        }
        return response()->json(new ReturnStatus('error', 'missing image data'), 404);
    }
}
