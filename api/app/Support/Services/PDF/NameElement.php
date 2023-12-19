<?php

namespace App\Support\Services\PDF;

use App\Support\Services\PDFGenerator;

class NameElement extends TextElement
{
    public function generate($element)
    {
        $this->parse($element);
        $fname = $this->data?->firstname ?? '';
        $lname = $this->data?->lastname ?? '';
        $txt = $lname . ", " . $fname;
        if (isset($element->name)) {
            if ($element->name == 'first') {
                $txt = $fname;
            }
            else if ($element->name == 'last') {
                $txt = $lname;
            }
        }
        if (strlen(trim($txt))) {
            $this->insertText($txt);
        }
    }
}
