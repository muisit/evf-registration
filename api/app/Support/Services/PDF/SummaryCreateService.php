<?php

namespace App\Support\Services\PDF;

use App\Models\Accreditation;
use App\Models\Document;
use App\Models\Fencer;
use App\Support\Enums\PagePositions;
use App\Support\Services\PDFService;
use App\Support\Contracts\AccreditationRelation;

class SummaryCreateService
{
    private ?AccreditationRelation $model;
    private Document $document;
    private $accreditations;

    private ?array $files = [];
    private $pdf = null;

    public function __construct(Document $document)
    {
        $this->document = $document;

        \Log::debug("constructing CreateSummary for " . json_encode($document->config));
        $this->accreditations = Accreditation::whereIn('id', $document->config["accreditations"] ?? [])
            ->with(['fencer', 'template'])
            ->get();
        $this->model = PDFService::modelFactory($document->type, $document->type_id);
    }

    public function handle()
    {
        if (count($this->accreditations) == 0) {
            \Log::error("Creating empty summary document with no accreditations " . $this->document->getKey());
            return;
        }

        $this->files = $this->document->sortFiles();
        if (empty($this->files) || count($this->files) == 0) {
            \Log::error("Could not create summary document: no files with hashes found " . $this->document->getKey());
            return;
        }

        \Log::debug("creating PDF file");
        $this->createPDF();

        if (file_exists($this->document->getPath())) {
            \Log::debug("saving document hash");
            $this->document->hash = $this->document->createHash();
            $this->document->save();
        }
    }

    private function createPDF()
    {
        $this->pdf = app(PDFService::class)->makeFpdi();
        \Log::debug("created PDF");
        
        $currentposition = null;
        foreach ($this->files as $k => $v) {
            \Log::debug("placing accreditation " . json_encode($currentposition));
            $currentposition = $this->placeAccreditation($v, $currentposition);
        }

        $dirname = dirname($this->document->getPath());
        \Log::debug("creating directory $dirname");
        @mkdir($dirname, 0755, true);
        \Log::debug("saving file at " . $this->document->getPath());
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
        \Log::debug("template page option is " . json_encode($pageoption));

        $this->pdf->SetSourceFile($file);
        $templateId = $this->pdf->importPage(1);

        list($thisposition, $followingposition) = $this->positionPage($pageoption, $currentposition);
        if ($currentposition === null) {
            $size = $this->getPageSize($pageoption, $templateId);
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
        \Log::debug("getPageSize for " . json_encode([$pageoption, $templateId]) . ' returns ' . json_encode($size));
        return $size;
    }
}
