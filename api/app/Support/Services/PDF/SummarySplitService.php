<?php

namespace App\Support\Services\PDF;

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
        $documents = $this->findExistingDocuments();
        $accreditations = $this->findAccreditations();
        if (count($accreditations) == 0) {
            foreach ($documents as $doc) {
                $doc->delete();
            }
            return [];
        }

        $batches = $this->splitAccreditations($accreditations);
        $documents = $this->createSummaryDocuments($batches, $documents);
        return $documents;
    }

    private function splitAccreditations(array $accreditations)
    {
        if (count($accreditations) < self::ACCREDITATIONS_PER_DOC) {
            return [0 => $accreditations];
        }

        usort($accreditations, fn(Fencer $a1, Fencer $a2) => $a1->fencer->getFullName() <=> $a2->fencer->getFullName());
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
        $newDocs = [];
        foreach ($batches as $batch) {
            // see if we can find an oldDocument with the same selection of docs
            $found = null;
            foreach ($documents as $oldDoc) {
                $accreditations = $oldDoc->config["accreditations"] ?? [];
                if (count($accreditations) == count($batch)) {
                    $batchIds = array_map(fn ($a) => $a->getKey(), array_values($batch));
                    $diff = array_diff(array_values($accreditations), $batchIds);
                    if (empty($diff)) {
                        $found = $oldDoc;
                        break;
                    }
                }
            }

            if ($found) {
                $newDocs[] = $found;
                $documents = array_filter($documents, fn($doc) => $doc->getKey() !== $found->getKey());
            }
            else {
                $doc = $this->createSummaryDocument();
                $doc->config["accreditations"] = array_map(fn ($accr) => $accr->getKey(), array_values($batch));
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
        $doc->name = $this->createName();
        $doc->config["event"] = $this->event->getKey();
        $doc->config["type"] = $this->type;
        $doc->config["typeId"] = $this->model->getKey(); // works because it is shared between all App\Models\Model
        $doc->save();
        $doc->path = $doc->name . "_" . $doc->getKey() . ".pdf";
        $doc->save();
    }

    private function createName()
    {
        return PDFService::summaryName($this->event->getKey(), $this->type, $this->model->getKey());
    }

    private function findExistingDocuments()
    {
        return $this->event->documents;
    }
}