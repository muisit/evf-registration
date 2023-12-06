<?php

namespace App\Http\Controllers\Fencers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fencer;
use App\Models\Schemas\ReturnStatus;
use App\Support\Services\PhotoAssessService;

class PhotoSave extends Controller
{
    /**
     * Store fencer associated picture to storage
     *
     * @OA\Post(
     *     path = "/fencers/{fencerId}/photo",
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
     *       @OA\JsonContent(ref="#/components/schemas/ReturnStatus")
     *   ),
     * )
     */
    public function index(Request $request, string $fencerId)
    {
        $fencer = Fencer::where('fencer_id', $fencerId)->first();
        if (empty($fencer) || empty($request->file('picture'))) {
            $this->authorize('not/ever');
        }
        $this->authorize('update', $fencer);

        if ($request->hasFile('picture')) {
            $imageLocation = $fencer->image();
            $mimeType = $request->file('picture')->getMimeType();
            $request->file('picture')->move(dirname($imageLocation), basename($imageLocation));

            $filename = PhotoAssessService::convert($imageLocation, $mimeType);
            if (!empty($filename)) {
                $fencer->fencer_picture = 'Y';
                $fencer->save();
                return response()->json(new ReturnStatus('ok'));
            }
            else {
                if (file_exists($imageLocation)) {
                    @unlink($imageLocation);
                }
                return response()->json(new ReturnStatus('error', 'corrupt image detected'));
            }
        }
        return response()->json(new ReturnStatus('error', 'missing image data'));
    }
}
