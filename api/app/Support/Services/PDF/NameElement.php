<?php

namespace App\Support\Services\PDF;

use App\Support\Services\PDFGenerator;

class NameElement extends TextElement
{
    private $generator;

    public function __construct(PDFGenerator $generator)
    {
        $this->generator = $generator;
    }

    public function generate($element, $data)
    {
        $this->parse($element);
        $fname = $data->firstname ?? '';
        $lname = $data->lastname ?? '';
        $txt = $lname . ", " . $fname;
        if (isset($element->name)) {
            if ($element->name == 'first') {
                $txt = $fname;
            }
            else if ($element->name == 'last') {
                $txt = $fname;
            }
        }
        if (strlen(trim($txt))) {
            $this->insertText($txt);
        }
    }
}
