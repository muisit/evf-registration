<?php

namespace App\Models\Schemas;

/**
 * Validation result
 *
 * @OA\Schema()
 */
class ValidationField
{
    /**
     * Field name
     *
     * @var string
     * @OA\Property()
     *
     */
    public string $field;

    /**
     * Validation messages for this field
     *
     * @var array
     * @OA\Property(
     *   type="array",
     *   @OA\Items(type="string")
     * )
     */
    public array $messages;
}
