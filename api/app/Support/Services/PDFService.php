<?php

namespace App\Support\Services;

use App\Models\Event;

class PDFService
{
    public static function summaryName(int $eventId, string $type, int $modelId)
    {
        return "summary_" . $eventId . "_" . $type . "_" . $modelId . '$';
    }

    public static function pdfPath(Event $event, $subpath = null)
    {
        $path = "pdfs/event" . $event->getKey() . ($subpath ? '/' . $subpath : '');
        return storage_path($path);
    }
}
