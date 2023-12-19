<?php

namespace App\Support\Services\PDF;

use App\Support\Services\PDFGenerator;

class QRCode extends Element
{
    private $generator;

    public function __construct(PDFGenerator $generator)
    {
        $this->generator = $generator;
    }

    public function generate($el, $data)
    {
        $this->parse($el);
        $link = $el->link ?? "";
        $link = $this->replaceParametersInLink($link, $data);

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
                $data->country ?? '',
                $data->category ?? '',
                $data->organisation ?? '',
                $data->lastname ?? '',
                $data->firstname ?? ''
            ],
            $link
        );
    }
}
