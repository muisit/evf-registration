<?php

namespace App\Models\Schemas\FE;

/**
 * Response for the WP front end interface
 *
 * @OA\Schema()
 */
class Response
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
    public string $status;

    /**
     * In case of errors, this can possibly contain an informative error message.
     *
     * @var string
     * @OA\Property()
     */
    public ?string $message;

    public function __construct($status, ?string $message = null)
    {
        $this->success = $status == 'ok';
        $this->status = $status;
        $this->message = $message;
    }
}
