<?php

namespace App\Support\Services\PDF;

use App\Models\Accreditation;
use App\Models\Document;
use App\Models\Event;
use App\Models\Fencer;
use App\Support\Enums\PagePositions;
use App\Support\Services\PDFService;
use App\Support\Contracts\AccreditationRelation;

class SummaryCreateService
{
    private AccreditationRelation $model;
    private Document $document;
    private Event $event;
    private string $type;
    private $accreditations;

    private $overallhash = null;
    private array $files = [];
    private $pdf = null;

    public function __construct(Document $document)
    {
        $this->document = $document;

        $this->accreditations = Accreditation::whereIn('id', $document->config["accreditations"] ?? [])
            ->with(['fencer', 'event', 'template'])
            ->get();
        $this->type = $document->config['type'] ?? '';
        $typeId = $document->config['typeId'] ?? 0;
        $this->model = PDFService::modelFactory($this->type, $typeId);
        $this->event = $this->document->event;
    }

    public function handle()
    {
        if (count($this->accreditations) == 0) {
            \Log::error("Creating empty summary document with no accreditations " . $this->document->getKey());
            return;
        }

        $this->createHash();
        if (count($this->files) == 0) {
            \Log::error("Could not create summary document: no files with hashes found " . $this->document->getKey());
            return;
        }

        $this->createPDF();

        if (file_exists($this->document->getPath())) {
            $this->document->hash = $this->overallhash;
            $this->document->save();
        }
    }

    private function createHash()
    {
        $this->files = [];
        // check that all files exist
        foreach ($this->accreditations as $a) {
            if (!empty($a->is_dirty)) {
                \Log::error("Dirty accreditation prevents creating summary file " . $this->document->getKey());
                return;
            }

            $path = $a->path();
            if (!file_exists($path)) {
                \Log::error("missing PDF $path prevents creation of summary file " . $this->document->getKey());
                $a->is_dirty = date('Y-m-d H:i:s');
                $a->save();
                return;
            }
        }

        foreach ($this->accreditations as $a) {
            $hash = $a->file_hash;
            $key = $a->fencer->getFullName() . "~" . $a->getKey();
            $this->files[$key] = ["file" => $a->path(), "hash" => $hash, "accreditation" => $a];
        }

        // sort the files by fencer name
        // Sorting makes it easier for the end user to find missing accreditations
        // Also, sorting is vital to make sure the overall hash is created in the
        // same way
        ksort($this->files, SORT_NATURAL);

        // accumulate all hashes to get at an overall hash
        $acchash = "";
        foreach ($this->files as $k => $v) {
            $acchash .= $v["hash"];
        }
        $this->overallhash = hash('sha256', $acchash);
    }

    private function createPDF()
    {
        $this->pdf = app(PDFService::class)->makeFpdi();
        
        $currentposition = null;
        foreach ($this->files as $k => $v) {
            $currentposition = $this->placeAccreditation($v, $currentposition);
        }

        $this->pdf->Output($this->document->getPath(), 'F');
    }

    private function placeAccreditation(array $v, ?PagePositions $currentposition): ?PagePositions
    {
        $accreditation = $v["accreditation"];
        $file = $v["file"];
        $hash = $v['hash'];
        $template = $accreditation->template;

        $pageoption = "a4portrait";
        if (!empty($template)) {
            $content = json_decode($template->content, true);
            if (isset($content["print"])) {
                $pageoption = $content["print"];
            }
        }

        $this->pdf->SetSourceFile($file);
        $templateId = $this->pdf->importPage(1);

        list($thisposition, $followingposition) = $this->positionPage($pageoption, $currentposition);
        if ($currentposition === null) {
            $size = $this->getPageSize($this->pdf, $pageoption, $templateId);
            $this->pdf->AddPage($size['orientation'], $size);
        }

        list($x, $y, $w, $h) = $this->placePage($thisposition);
        $this->pdf->useImportedPage($templateId, $x, $y, $w, $h, false);
        return $followingposition;
    }

    public function positionPage(string $pageoption, ?PagePositions $currentposition): array
    {
        $thisposition = null;
        $followingposition = null;
        switch ($pageoption) {
            default:
            case 'a4portrait':
                if ($currentposition === null) {
                    $thisposition = PagePositions::A4_1;
                    $followingposition = PagePositions::A4_3;
                }
                else if ($currentposition === PagePositions::A4_3) {
                    $thisposition = PagePositions::A4_3;
                    $followingposition = null; // new page
                }
                break;
            case 'a4landscape':
                $thisposition = PagePositions::A4L_1;
                $followingposition = null;
                break;
            case 'a4portrait2':
                // allow 1, 2 and 3
                if ($currentposition === null) {
                    $thisposition = PagePositions::A4_1;
                    $followingposition = PagePositions::A4_2;
                }
                else if ($currentposition === PagePositions::A4_2) {
                    $thisposition = PagePositions::A4_2;
                    $followingposition = PagePositions::A4_3;
                }
                else if ($currentposition === PagePositions::A4_3) {
                    $thisposition = PagePositions::A4_3;
                    $followingposition = PagePositions::A4_4;
                }
                else if ($currentposition === PagePositions::A4_4) {
                    $thisposition = PagePositions::A4_4;
                    $followingposition = null; // new page
                }
                break;
            case 'a4landscape2':
                if ($currentposition === null) {
                    $thisposition = PagePositions::A4L_1;
                    $followingposition = PagePositions::A4L_2;
                }
                else if ($currentposition === PagePositions::A4L_2) {
                    $thisposition = PagePositions::A4L_2;
                    $followingposition = null;
                }
                break;
            case 'a5landscape':
                $thisposition = PagePositions::A5L_1;
                $followingposition = null;
                break;
            case 'a5landscape2':
                if ($currentposition === null) {
                    $thisposition = PagePositions::A5L_1;
                    $followingposition = PagePositions::A5L_2;
                }
                else if ($currentposition === PagePositions::A5L_2) {
                    $thisposition = PagePositions::A5L_2;
                    $followingposition = null;
                }
                break;
            case 'a6portrait':
                $thisposition = PagePositions::A6;
                $followingposition = null;
                break;
        }
        return [$thisposition, $followingposition];
    }

    public function placePage(?PagePositions $position): array
    {
        $x = 0;
        $y = 0;
        $w = 210;
        $h = 297;
        switch ($position) {
            case PagePositions::A4_1:
                break; // no adjustments
            case PagePositions::A4_2:
                $x = 105;
                break;
            case PagePositions::A4_3:
                $y = 148.5;
                break;
            case PagePositions::A4_4:
                $x = 105;
                $y = 148.5;
                break;
            case PagePositions::A4L_1:
                $x = 43;
                $y = 31;
                $w = 297;
                $h = 210;
                break;
            case PagePositions::A4L_2:
                $x = 43 + 105;
                $y = 31;
                $w = 297;
                $h = 210;
                break;
            case PagePositions::A5L_1:
                $w = 210;
                $h = 148.5;
                break;
            case PagePositions::A5L_2:
                $x = 105;
                $w = 210;
                $h = 148.5;
                break;
            case PagePositions::A6:
                $w = 105;
                $h = 148.5;
                break;
        }
        return [$x, $y, $w, $h];
    }

    private function getPageSize($pageoption, $templateId)
    {
        $size = 0;
        switch ($pageoption) {
            default:
            case 'a4portrait':
            case 'a4portrait2':
            case 'a5landscape':
            case 'a5landscape2':
                $size = $this->pdf->getTemplateSize($templateId, 210);
                break;
            case 'a4landscape':
            case 'a4landscape2':
                $size = $this->pdf->getTemplateSize($templateId, 297);
                break;
            case 'a6portrait':
                $size = $this->pdf->getTemplateSize($templateId, 105);
                break;
        }
        return $size;
    }
}
