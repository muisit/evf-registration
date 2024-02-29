<?php

namespace App\Models\Schemas;

use App\Models\AccreditationDocument as Model;
use Carbon\Carbon;

/**
 * AccreditationDocumentSummary information model
 *
 * @OA\Schema()
 */
class AccreditationDocumentSummary
{
    /**
     * ID of the record
     *
     * @var integer
     * @OA\Property()
     */
    public int $id = 0;

    /**
     * fencer id
     *
     * @var int
     * @OA\Property()
     */
    public ?int $fencerId = null;

    /**
     * Status of this document
     *
     * @var string
     * @OA\Property()
     */
    public ?string $status = null;

    /**
     * Check-in time
     *
     * @var string
     * @OA\Property()
     */
    public ?string $checkin = null;

    /**
     * Start Process time
     *
     * @var string
     * @OA\Property()
     */
    public ?string $processStart = null;

    /**
     * End Process time
     *
     * @var string
     * @OA\Property()
     */
    public ?string $processEnd = null;

    /**
     * Checkout time
     *
     * @var string
     * @OA\Property()
     */
    public ?string $checkout = null;

    /**
     * country id
     *
     * @var int
     * @OA\Property()
     */
    public ?int $countryId = null;

    public function __construct(?Model $doc)
    {
        if (!empty($doc)) {
            $this->id = $doc->getKey();
            $this->status = $doc->status;
            $this->checkin = (new Carbon($doc->checkin))->toDateTimeString();
            $this->processStart = $doc->process_start ? (new Carbon($doc->process_start))->toDateTimeString() : null;
            $this->processEnd = $doc->process_end ? (new Carbon($doc->process_end))->toDateTimeString() : null;
            $this->checkout = $doc->checkout ? (new Carbon($doc->checkout))->toDateTimeString() : null;
            $this->fencerId = $doc->accreditation->fencer->getKey();
            $this->countryId = $doc->accreditation->fencer->getCountryOfRegistration($doc->accreditation->event);
        }
    }
}
