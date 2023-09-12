<?php

namespace App\Models\Schemas\Requests;

/**
 * @OA\RequestBody(
 *     request="duplicate",
 *     required=true,
 *     description="Checking for fencer information duplication",
 *     @OA\JsonContent(ref="#/components/schemas/Fencer")
 * )
 */
class DuplicateRequestBody
{
}
