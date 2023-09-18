<?php

namespace App\Models\Schemas;

/**
 * Basic validation return value
 *
 * @OA\Schema()
 */
class ValidationStatus
{
    /**
     * Status value
     *
     * @var string
     * @OA\Property(
     *     enum = {"ok", "error"}
     * )
     *
     */
    public string $message;

    /**
     * In case of errors, this can possibly contain an informative error message.
     *
     * @var string
     * @OA\Property(
     *   type="array",
     *   @OA\Items(type="ValidationMessage")
     * )
     */
    public array $errors;

    public function __construct($status, ?string $message = null)
    {
        $this->status = $status;
        $this->messages = [];
    }

    public function add(string $field, string $message)
    {
        $this->messages[] = new ValidationField($field, $message);
    }
}
