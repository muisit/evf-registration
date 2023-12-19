<?php

namespace App\Support\Services;

use App\Models\Accreditation;
use App\Models\AccreditationTemplate;
use App\Models\Event;
use App\Models\Fencer;
use App\Support\Services\PDF\AccreditationID;
use App\Support\Services\PDF\Box;
use App\Support\Services\PDF\CategoryElement;
use App\Support\Services\PDF\CountryElement;
use App\Support\Services\PDF\CountryFlag;
use App\Support\Services\PDF\DatesElement;
use App\Support\Services\PDF\Image;
use App\Support\Services\PDF\NameElement;
use App\Support\Services\PDF\OrgElement;
use App\Support\Services\PDF\PhotoId;
use App\Support\Services\PDF\QRCode;
use App\Support\Services\PDF\RolesElement;
use App\Support\Services\PDF\TextElement;
use App\Support\Services\PDF\TCPDF;

class PDFGenerator
{
    const APP_WIDTH = 420.0; // 2x210, front-end canvas width
    const APP_HEIGHT = 594.0; // 2x297, front end canvas height
    const PDF_WIDTH = 105.0;// A6 portrait in mm
    const PDF_HEIGHT = 148.5; // A6 portrait in mm

    //const PDF_PXTOPT=1.76; // for A4 reports
    const PDF_PXTOPT = 1.01;
    const FONTPATH = "vendor/tecnickcom/tcpdf/fonts";

    public $pdf;
    private string $pageoption;
    public $accreditationId = null;
    public Accreditation $accreditation;

    public function __construct(Accreditation $accreditation)
    {
        $this->accreditation = $accreditation;
    }

    public function generate($data)
    {
        $this->pageoption = $data->print ?? 'a4portrait';
        $this->pdf = $this->createBasePDF();
        $this->pdf->AddPage();
        $template_id = $this->pdf->startTemplate(self::PDF_WIDTH, self::PDF_HEIGHT, true);
        $this->applyTemplate($data);
        $this->pdf->endTemplate();

        $this->copyTemplateOverPage($template_id);
    }

    protected function instantiatePDF()
    {
        $page = "A4";
        $orientation = "P";
        switch ($this->pageoption) {
            default:
            case 'a4portrait':
            case 'a4portrait2':
                $orientation = 'P';
                $page = 'A4';
                break;
            case 'a4landscape':
            case 'a4landscape2':
                $orientation = 'L';
                $page = 'A4';
                break;
            case 'a5landscape':
            case 'a5landscape2':
                $orientation = 'L';
                $page = 'A5';
                break;
            case 'a6portrait':
                $orientation = 'P';
                $page = 'A6';
                break;
        }
        /* last parameter: pdfa mode 3 */
        return new TCPDF($orientation, "mm", $page, true, 'UTF-8', false, 3);
    }

    protected function createBasePDF()
    {
        $pdf = $this->instantiatePDF();
        $pdf->SetCreator("European Veteran Fencing");
        $pdf->SetAuthor('European Veteran Fencing');
        $pdf->SetTitle('Accreditation for ' . $this->accreditation->event->event_title);
        $pdf->SetSubject($this->accreditation->fencer->fencer_surname . ", " . $this->accreditation->fencer->fencer_firstname);
        $pdf->SetKeywords('EVF, Accreditation,' . $this->accreditation->event->event_title);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(5, 5, 5);
        $pdf->SetAutoPageBreak(false);
        $pdf->setImageScale(1.25);
        $pdf->setFontSubsetting(true);
        $pdf->SetDefaultMonospacedFont('courier');
        // set to helvetica, always loaded
        $pdf->SetFont('helvetica', '', 14, '', true);
        return $pdf;
    }

    public function saveFile($path)
    {
        $dirname = dirname($path);
        @mkdir($dirname, 0755, true);
        $this->pdf->Output($path, 'F');
    }

    private function copyTemplateOverPage($template_id)
    {
        // If we print landscape instead of portrait, we have less badges and more margins
        // additional offset for landscape printing starting at 297 - (2x105) = 87/2 = 43
        $landscapeoffsetX = 43;
        // and landscape height: y = 210 - 148.5 = 61.5/2 = 31
        $landscapeoffsetY = 31;

        switch ($this->pageoption) {
            default:
            case 'a4portrait':
                // paste the template twice at the top
                $this->pdf->printTemplate($template_id, $x = 0, $y = 0, $w = self::PDF_WIDTH, $h = self::PDF_HEIGHT, $align = '', $palign = '', $fitonpage = false);
                $this->pdf->printTemplate($template_id, $x = self::PDF_WIDTH, $y = 0, $w = self::PDF_WIDTH, $h = self::PDF_HEIGHT, $align = '', $palign = '', $fitonpage = false);
                break;
            case 'a4landscape':
                // print the template twice over the centre of the page
                $this->pdf->printTemplate($template_id, $x = $landscapeoffsetX, $y = $landscapeoffsetY, $w = self::PDF_WIDTH, $h = self::PDF_HEIGHT, $align = '', $palign = '', $fitonpage = false);
                $this->pdf->printTemplate($template_id, $x = $landscapeoffsetX + self::PDF_WIDTH, $y = $landscapeoffsetY, $w = self::PDF_WIDTH, $h = self::PDF_HEIGHT, $align = '', $palign = '', $fitonpage = false);
                break;
            case 'a4portrait2':
                // print the template once at the top
                $this->pdf->printTemplate($template_id, $x = 0, $y = 0, $w = self::PDF_WIDTH, $h = self::PDF_HEIGHT, $align = '', $palign = '', $fitonpage = false);
                break;
            case 'a4landscape2':
                // print the template once over the centre of the page
                $this->pdf->printTemplate($template_id, $x = $landscapeoffsetX, $y = $landscapeoffsetY, $w = self::PDF_WIDTH, $h = self::PDF_HEIGHT, $align = '', $palign = '', $fitonpage = false);
                break;
            case 'a5landscape':
                // print the template twice over the width
                $this->pdf->printTemplate($template_id, $x = 0, $y = 0, $w = self::PDF_WIDTH, $h = self::PDF_HEIGHT, $align = '', $palign = '', $fitonpage = false);
                $this->pdf->printTemplate($template_id, $x = self::PDF_WIDTH, $y = 0, $w = self::PDF_WIDTH, $h = self::PDF_HEIGHT, $align = '', $palign = '', $fitonpage = false);
                break;
            case 'a5landscape2':
                // print the template once over the width
                $this->pdf->printTemplate($template_id, $x = 0, $y = 0, $w = self::PDF_WIDTH, $h = self::PDF_HEIGHT, $align = '', $palign = '', $fitonpage = false);
                break;
            case 'a6portrait':
                // print the template once at the top
                $this->pdf->printTemplate($template_id, $x = 0, $y = 0, $w = self::PDF_WIDTH, $h = self::PDF_HEIGHT, $align = '', $palign = '', $fitonpage = false);
                break;
        }

        // put the Accreditation ID either on both sides, only left or only right
        if (!empty($this->accreditationId)) {
            $options['align'] = 'C';

            $offset1 = array($this->accreditationId->options["offset"][0], $this->accreditationId->options["offset"][1]);
            $offset2 = array($this->accreditationId->options["offset"][0], $this->accreditationId->options["offset"][1]);
            switch ($this->pageoption) {
                default:
                case 'a4portrait':
                    $offset2[0] = $offset2[0] + self::PDF_WIDTH;
                    break;
                case 'a4landscape':
                    $offset1[0] = $offset1[0] + $landscapeoffsetX;
                    $offset1[1] = $offset1[1] + $landscapeoffsetY;
                    $offset2[0] = $offset2[0] + $landscapeoffsetX + self::PDF_WIDTH;
                    $offset2[1] = $offset2[1] + $landscapeoffsetY;
                    break;
                case 'a4portrait2':
                    $offset2 = null;
                    break;
                case 'a4landscape2':
                    $offset1[0] = $offset1[0] + $landscapeoffsetX;
                    $offset1[1] = $offset1[1] + $landscapeoffsetY;
                    $offset2 = null;
                    break;
                case 'a5landscape':
                    $offset2[0] = $offset2[0] + self::PDF_WIDTH;
                    break;
                case 'a5landscape2':
                    $offset2 = null;
                    break;
                case 'a6portrait':
                    $offset2 = null;
                    break;
            }

            if ($this->accreditationId->side == "both" || $this->accreditationId->side == "left") {
                $options["offset"] = $offset1;
                $this->accreditationId->generate();
            }
            if (!empty($offset2) && ($this->accreditationId->side == "both" || $this->accreditationId->side == "right")) {
                $options["offset"] = $offset2;
                $this->accreditationId->generate();
            }
        }
    }

    private function applyTemplate($data)
    {
        // for testing purposes, we need to be able to set the time/date
        $this->pdf->setDocCreationTimestamp($data->created ?? time());
        $this->pdf->setDocModificationTimestamp($data->modified ?? time());
        $templateSpecification = json_decode($this->accreditation->template->content);
        $layers = array();

        if (isset($templateSpecification->elements)) {
            foreach ($templateSpecification->elements as $el) {
                $style = $el->style ?? (object)[];
                $name = "l0";
                if (isset($style->zIndex)) {
                    $name = "l" . $style->zIndex;
                }
                if (!isset($layers[$name])) {
                    $layers[$name] = [];
                }
                $layers[$name][] = $el;
            }
        }

        $pictures = [];
        if (isset($templateSpecification->pictures)) {
            foreach ($templateSpecification->pictures as $pic) {
                if (isset($pic->file_id)) {
                    $pictures[$pic->file_id] = $pic;
                }
            }
        }

        $keys = array_keys($layers);
        natsort($keys);

        foreach ($keys as $key) {
            foreach ($layers[$key] as $el) {
                switch ($el->type ?? 'none') {
                    case "photo":
                        (new PhotoId($this))->generate($el);
                        break;
                    case "text":
                        (new TextElement($this))->generate($el);
                        break;
                    case "name":
                        (new NameElement($this))->withData($data)->generate($el);
                        break;
                    case "accid":
                        (new AccreditationId($this))->withLabel($this->accreditation->fe_id)->generate($el);
                        break;
                    case "category":
                        (new CategoryElement($this))->withData($data)->generate($el);
                        break;
                    case "country":
                        (new CountryElement($this))->withData($data)->generate($el);
                        break;
                    case "cntflag":
                        (new CountryFlag($this))->withData($data)->generate($el);
                        break;
                    case "org":
                        (new OrgElement($this))->withData($data)->generate($el);
                        break;
                    case "roles":
                        (new RolesElement($this))->withData($data)->generate($el);
                        break;
                    case "dates":
                        (new DatesElement($this))->withData($data)->generate($el);
                        break;
                    case "box":
                        (new Box($this))->generate($el);
                        break;
                    case "img":
                        (new Image($this))->withData($pictures)->generate($el);
                        break;
                    case 'qr':
                        (new QRCode($this))->withData($data)->generate($el);
                        break;
                    default:
                        \Log::error("PDF: unknown element for rendering " . $el->type);
                        break;
                }
            }
        }
    }
}
