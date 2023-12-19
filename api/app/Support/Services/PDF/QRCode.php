<?php

namespace App\Support\Services\PDF;

use App\Support\Services\PDFGenerator;

class QRCode extends Element
{
    public function generate($el)
    {
        $this->parse($el);
        $link = $el->link ?? "";
        $link = $this->replaceParametersInLink($link, $this->data);

        $style = [
            'border' => 2,
            'vpadding' => 'auto',
            'hpadding' => 'auto',
            'fgcolor' => [0, 0, 0],
            'bgcolor' => [255,255,255],
            'module_width' => 1, // width of a single module in points
            'module_height' => 1 // height of a single module in points
        ];

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
    }

    private function replaceParametersInLink($link, $data)
    {
        return str_replace(
            [
                "%i",
                "%e",
                "%a",
                "%c",
                "%v",
                "%o",
                "%l",
                "%f"
            ],
            [
                $this->generator->accreditation->fencer->fencer_id,
                $this->generator->accreditation->event->event_id,
                $this->generator->accreditation->fe_id ?? "",
                $data?->country ?? '',
                $data?->category ?? '',
                $data?->organisation ?? '',
                $data?->lastname ?? '',
                $data?->firstname ?? ''
            ],
            $link
        );
    }
}
