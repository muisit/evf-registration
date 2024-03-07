<?php

namespace App\Support\Services\PDF;

use App\Models\Accreditation;
use App\Models\Document;
use App\Models\Event;
use App\Models\Fencer;
use App\Support\Services\PDFService;
use App\Support\Contracts\AccreditationRelation;

class SummarySplitService
{
    const ACCREDITATIONS_PER_DOC = 50;
    private AccreditationRelation $model;
    private Event $event;
    private string $type;

    public function __construct(Event $event, string $type, AccreditationRelation $model)
    {
        $this->event = $event;
        $this->type = $type;
        $this->model = $model;
    }

    public function handle()
    {
        \Log::debug("handling SummarySplit");
        $documents = $this->findExistingDocuments();
        \Log::debug("found " . count($documents) . ' existing documents');
        $accreditations = $this->findAccreditations();
        if (count($accreditations) == 0) {
            \Log::debug("no accreditations found, removing all documents");
            foreach ($documents as $doc) {
                $doc->delete();
            }
            return [];
        }

        $batches = $this->splitAccreditations($accreditations);
        $documents = $this->createSummaryDocuments($batches, $documents);
        return $documents;
    }

    private function splitAccreditations($accreditations)
    {
        \Log::debug("splitAccreditations for " . json_encode($accreditations->pluck('id')));
        $accreditations = $accreditations->all();
        if (count($accreditations) < self::ACCREDITATIONS_PER_DOC) {
            return [$accreditations];
        }

        \Log::debug("accreditations is " . count($accreditations));
        usort($accreditations, fn(Accreditation $a1, Accreditation $a2) => $a1->fencer->getFullName() <=> $a2->fencer->getFullName());
        $accreditations = array_values($accreditations); // is this necessary?

        $pages = ceil(count($accreditations) / self::ACCREDITATIONS_PER_DOC);
        $docsPerPage = ceil(count($accreditations) / $pages);

        $docs = array();
        for ($i = 0; $i < count($accreditations); $i++) {
            $pageIndex = floor($i / $docsPerPage);
            if (!isset($docs[$pageIndex])) {
                $docs[$pageIndex] = [];
            }
            $docs[$pageIndex][] = $accreditations[$i];
        }
        return $docs;
    }

    private function findAccreditations()
    {
        return $this->model->selectAccreditations($this->event);
    }

    private function createSummaryDocuments($batches, $documents)
    {
        \Log::debug("creating summary documents using " . count($batches));
        $newDocs = [];
        foreach ($batches as $index => $batch) {
            $batchIds = array_map(fn ($a) => $a->getKey(), array_values($batch));
            // see if we can find an oldDocument with the same selection of docs
            $found = null;
            foreach ($documents as $oldDoc) {
                $accreditations = $oldDoc->config["accreditations"] ?? [];
                if (count($accreditations) == count($batch)) {
                    $diff = array_diff(array_values($accreditations), $batchIds);
                    if (empty($diff)) {
                        $found = $oldDoc;
                        break;
                    }
                }
            }

            if ($found) {
                $newDocs[] = $found;
                $documents = $documents->filter(fn($doc) => $doc->getKey() !== $found->getKey());
            }
            else {
                $doc = $this->createSummaryDocument();
                \Log::debug("creating list of accreditations in batch of size " . json_encode($batchIds));
                $doc->setConfig(["accreditations" => $batchIds]);
                $doc->save();
                $newDocs[] = $doc;
            }
        }

        foreach ($documents as $doc) {
            $doc->delete();
        }
        return $newDocs;
    }

    private function createSummaryDocument()
    {
        $doc = new Document();
        $doc->event_id = $this->event->getKey();
        $doc->type = $this->type;
        $doc->type_id = $this->model->getKey(); // works because it is shared between all App\Support\Contracts\AccreditationRelation
        $doc->config = [];
        $doc->save();
        $doc->path = $this->createName($doc);
        $doc->save();
        return $doc;
    }

    private function createName(Document $doc)
    {
        $name = '';
        switch ($doc->type) {
            case 'Country': $name = $this->model->country_abbr; break;
            case 'Role': $name = $this->model->role_name; break;
            case 'Event': $name = $this->model->title; break;
            case 'Template': $name = $this->model->name;
        }
        return $doc->type . "_" . str_replace(' ', '_', $name) . '_' . $doc->getKey() . ".pdf";
    }

    private function findExistingDocuments()
    {
        return $this->event->documents()->where('type', $this->type)->where('type_id', $this->model->getKey())->get();
    }
}
