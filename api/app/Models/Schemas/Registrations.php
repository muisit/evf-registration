<?php

namespace App\Models\Schemas;

use App\Models\Accreditation;
use App\Models\AccreditationDocument;
use App\Models\Registration as BaseModel;

/**
 * EventType model
 *
 * @OA\Schema()
 */
class Registrations
{
    /**
     * Contained registrations
     *
     * @var Registration[]
     * @OA\Property(
     *   type="array",
     *   @OA\Items(type="Registration")
     * )
     */
    public ?array $registrations = null;

    /**
     * Contained fencers
     *
     * @var Fencer[]
     * @OA\Property(
     *   type="array",
     *   @OA\Items(type="Fencer")
     * )
     */
    public ?array $fencers = null;

    /**
     * Contained accreditation documents
     *
     * @var AccreditationDocumentSummary[]
     * @OA\Property(
     *   type="array",
     *   @OA\Items(type="AccreditationDocumentSummary")
     * )
     */
    public ?array $documents = null;

    public function add(BaseModel $registration, $documents = [])
    {
        $docsByFencerId = [];
        foreach ($documents as $doc) {
            $key = 'f' . $doc->accreditation->fencer_id;
            if (!isset($docsByFencerId[$key])) {
                $docsByFencerId[$key] = [];
            }
            $docsByFencerId[$key][] = $doc;
        }
        if (empty($this->registrations)) {
            $this->registrations = [];
        }
        $this->registrations[] = new Registration($registration);

        $fencer = $registration->fencer;

        if (!empty($fencer)) {
            if (empty($this->fencers)) {
                $this->fencers = [];
            }
            if (!isset($this->fencers[$fencer->getKey()]) && isset($docsByFencerId['f' . $fencer->getKey()])) {
                foreach ($docsByFencerId['f' . $fencer->getKey()] as $doc) {
                    $this->documents[] = new AccreditationDocumentSummary($doc);
                }
            }
            $this->fencers[$fencer->getKey()] = new Fencer($fencer);
        }
    }

    public function finalize()
    {
        if (!empty($this->fencers)) {
            $this->fencers = array_values($this->fencers);
        }
    }
}
