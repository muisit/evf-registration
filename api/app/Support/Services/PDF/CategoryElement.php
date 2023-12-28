<?php

namespace App\Support\Services\PDF;

use App\Support\Services\PDFGenerator;

class CategoryElement extends TextElement
{
    public function generate($el)
    {
        $this->parse($el);
        $txt = $this->data?->category ?? '';
        if (strlen(trim($txt))) {
            $this->insertText($txt);
        }
    }
}
