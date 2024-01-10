<?php

namespace App\Models\Schemas\Requests;

/**
 * @OA\RequestBody(
 *     request="fencer",
 *     required=true,
 *     description="Fencer Photo status data",
 *     @OA\JsonContent(ref="#/components/schemas/FencerPhoto")
 * )
 */
class FencerPhoto
{
}
