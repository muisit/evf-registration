<?php

namespace App\Support\Services\PDF;

use App\Support\Services\PDFGenerator;

class AccreditationID extends TextElement
{
    private $generator;
    public $options;
    public $side;
    public $label;

    public function __construct(PDFGenerator $generator)
    {
        $this->generator = $generator;
    }

    public function prepare($el, $label)
    {
        $this->parse($el);
        $this->label = $label;
        $this->side = $element->side ?? 'both';
        return $this;
    }

    public function generate($options)
    {
        // the accreditation ID consists of a QR code and the AccID underneath it
        $style = [
            'border' => 2,
            'vpadding' => 'auto',
            'hpadding' => 'auto',
            'fgcolor' => [0, 0, 0],
            'bgcolor' => [255,255,255],
            'module_width' => 1, // width of a single module in points
            'module_height' => 1 // height of a single module in points
        ];

        $link = get_site_url(null, "/accreditation/" . $this->label, "https");
        // QRCODE,H : QR-CODE Best error correction
        $this->generator->pdf->write2DBarcode(
            $link,
            'QRCODE,H',
            $this->offset[0],
            $this->offset[1],
            $this->size[0],
            $this->size[1],
            $style,
            'N'
        );

        // put the text below, 2mm margin
        $this->offset[1] += $this->size[1];
        $this->insertText($this->label);
    }
}
