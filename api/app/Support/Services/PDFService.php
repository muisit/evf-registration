<?php

namespace App\Support\Services;

use App\Models\Event;

class PDFService
{
    public static function summaryName(int $eventId, string $type, int $modelId)
    {
        return "summary_" . $eventId . "_" . $type . "_" . $modelId . '$';
    }

    public static function generate($path, $specification)
    {
        (new PDFGenerator($path))->generate($specification);
    }
}
