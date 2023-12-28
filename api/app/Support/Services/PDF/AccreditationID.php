<?php

namespace App\Support\Services\PDF;

use App\Support\Services\PDFGenerator;

class AccreditationID extends TextElement
{
    public $side;
    public $label;

    public function withLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    public function generate($el)
    {
        $this->parse($el);
        $this->side = $el->side ?? 'both';
        $this->generator->accreditationId = $this;
        return $this;
    }

    public function options()
    {
        $obj = parent::options();
        $obj->side = $this->side ?? 'both';
        $obj->label = $this->label ?? '';
        return $obj;
    }

    public function finalise()
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

        $link = $this->label;
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
