<?php

namespace App\Models\Schemas\Requests;

/**
 * @OA\RequestBody(
 *     request="accreditationtemplate",
 *     required=true,
 *     description="AccreditationTemplate data",
 *     @OA\JsonContent(ref="#/components/schemas/AccreditationTemplate")
 * )
 */
class AccreditationTemplate
{
}
