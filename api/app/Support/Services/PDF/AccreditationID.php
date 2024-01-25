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

    public function finalise($options)
    {
        if (isset($options['offset'])) {
            $this->offset = $options['offset'];
        }

        // the accreditation ID consists of a 1D barcode and the AccID underneath it
        $style = [
            'border' => 2,
            'vpadding' => '2',
            'hpadding' => '2',
            'fgcolor' => [0, 0, 0],
            'bgcolor' => false, // set to false for transparent
            //'position' => 'C', // alignment of the barcode wrt to page
            'align' => 'C', // alignment of the label
            'stretch' => false, // stretch the code to fill the box, allowing us to use box width to control this
            'text' => false,
        ];

        $size = 14 - 2 - strlen($this->label);
        if ($size < 0) $size = 0;
        $link = sprintf("11%s%s", $this->label, str_repeat('0', $size));
        // QRCODE,H : QR-CODE Best error correction
        $originalSize = $this->size;
        $originalOffset = $this->offset;
        $this->size[1] = 2/3 * $originalSize[1]; // 2/3rds for the code, 1/3rd for the label
        $this->generator->pdf->write1DBarcode(
            $link,
            'I25+',
            $this->offset[0],
            $this->offset[1],
            $this->size[0],
            $this->size[1],
            null, // bar width, default 0.4mm
            $style,
            'T' // position box top at the pointer
        );

        // put the text below
        $this->offset[1] += $this->size[1];
        $this->size[1] = $this->size[1] / 2; // half of 2/3rds for the label
        $this->alignment = 'C';
        $this->outOfTemplate = true;
        $this->insertText($link);

        $this->size = $originalSize;
        $this->offset = $originalOffset;
    }
}
