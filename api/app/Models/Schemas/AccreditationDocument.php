<?php

namespace App\Models\Schemas;

use App\Models\AccreditationDocument as Model;
use Carbon\Carbon;

/**
 * AccreditationDocument information model
 *
 * @OA\Schema()
 */
class AccreditationDocument
{
    /**
     * ID of the record
     *
     * @var integer
     * @OA\Property()
     */
    public int $id = 0;

    /**
     * Related accreditation badge code
     *
     * @var string
     * @OA\Property()
     */
    public ?string $badge = null;

    /**
     * Related card number
     *
     * @var string
     * @OA\Property()
     */
    public ?string $card = null;

    /**
     * Related document number
     *
     * @var string
     * @OA\Property()
     */
    public ?string $document = null;

    /**
     * JSON payload settings
     *
     * @var string
     * @OA\Property()
     */
    public $payload = null;

    /**
     * Creation time
     *
     * @var string
     * @OA\Property()
     */
    public ?string $entered = null;

    public function __construct(?Model $doc)
    {
        if (!empty($doc)) {
            $this->id = $doc->getKey();
            $this->badge = $doc->accreditation->getFullAccreditationId();
            $this->card = $doc->card ?? null;
            $this->document = $doc->document_nr ?? null;
            $this->payload = json_encode($doc->payload ?? []);
            $this->entered = (new Carbon($doc->created_at))->toDateTimeString();
        }
    }
}
