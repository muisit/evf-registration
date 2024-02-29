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
     * fencer id
     *
     * @var int
     * @OA\Property()
     */
    public ?int $fencerId = null;

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
     * Status of this document
     *
     * @var string
     * @OA\Property()
     */
    public ?string $status = null;

    /**
     * JSON payload settings
     *
     * @var string
     * @OA\Property()
     */
    public $payload = null;

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
     * Dates at which the associated accreditation is valid (competition days)
     * 
     * @var array
     * @OA\Property(type="array",
     *   @OA\Items(type="string"))
     */
    public array $dates = [];

    /**
     * Fencer name
     *
     * @var string
     * @OA\Property()
     */
    public ?string $name = null;

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
            $this->fencerId = $doc->accreditation->fencer->getKey();
            $this->badge = $doc->accreditation->getFullAccreditationId();
            $this->card = $doc->card ?? null;
            $this->document = $doc->document_nr ?? null;
            $this->status = $doc->status;
            $this->payload = json_encode($doc->payload ?? []);
            $this->checkin = (new Carbon($doc->checkin))->toDateTimeString();
            $this->processStart = $doc->process_start ? (new Carbon($doc->process_start))->toDateTimeString() : null;
            $this->processEnd = $doc->process_end ? (new Carbon($doc->process_end))->toDateTimeString() : null;
            $this->checkout = $doc->checkout ? (new Carbon($doc->checkout))->toDateTimeString() : null;
            $this->dates = $doc->accreditation->getDatesOfAccreditation();
            $this->name = $doc->accreditation->fencer->getFullName();
            $this->countryId = $doc->accreditation->fencer->getCountryOfRegistration($doc->accreditation->event);
        }
    }
}
