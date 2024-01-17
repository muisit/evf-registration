<?php

namespace App\Http\Controllers\Templates;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccreditationTemplate;
use Auth;
use Carbon\Carbon;

class Picture extends Controller
{
    /**
     * Return a picture associated with the indicated template
     *
     * @OA\Get(
     *     path = "/templates/{templateId}/picture/{pictureId}",
     *     @OA\Response(
     *         response = "200",
     *         description = "Successful save",
     *         @OA\Schema(@OA\Property(type="string", format="binary"))
     *     ),
     *     @OA\Response(
     *         response  = "403",
     *         description = "Access not allowed",
     *     )
     * )
     */
    public function index(Request $request, string $templateId, string $pictureId)
    {
        $template = AccreditationTemplate::where('id', $templateId)->first();
        if (empty($template)) {
            $this->authorize('not/ever');
        }
        $this->authorize('view', $template);

        \Log::debug("looking for picture $pictureId");
        $content = json_decode($template->content);
        if (isset($content->pictures)) {
            foreach ($content->pictures as $picture) {
                if ($picture->file_id == $pictureId) {
                    $imageLocation = $template->image($pictureId, $picture->file_ext);
                    if (!empty($imageLocation) && file_exists($imageLocation) && is_readable($imageLocation)) {
                        $mimeType = 'image/jpeg';
                        if (isset($picture->file_mimetype)) {
                            $mimeType = $picture->file_mimetype;
                        }
                        else {
                            switch (strtolower($picture->file_ext)) {
                                default:
                                case 'jpg':
                                case 'jpeg':
                                    $mimeType = 'image/jpeg';
                                    break;
                                case 'png':
                                    $mimeType = 'image/png';
                                    break;
                                case 'gif':
                                    $mimeType = 'image/gif';
                                    break;
                            }
                        }
                        header('Content-Disposition: inline;');
                        header('Content-Type: ' . $mimeType);
                        header('Expires: ' . (time() + 2 * 24 * 60 * 60));
                        header('Cache-Control: must-revalidate');
                        header('Pragma: public');
                        header('Content-Length: ' . filesize($imageLocation));
                        readfile($imageLocation);
                    }
                }
            }
        }
        else {
            $this->authorize('not/ever');
        }
    }
}
