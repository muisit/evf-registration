<?php

namespace App\Support\Services;

use App\Models\Event;
use App\Models\AccreditationTemplate;
use App\Models\Role;
use App\Models\Country;
use App\Models\SideEvent;
use App\Support\Contracts\AccreditationRelation;
use App\Support\Services\PDF\SummarySplitService;
use App\Support\Services\PDF\SummaryCreateService;

class PDFService
{
    public static function summaryName(int $eventId, string $type, int $modelId)
    {
        return "summary_" . $eventId . "_" . $type . "_" . $modelId . '$';
    }

    public static function pdfPath(Event $event, $subpath = null)
    {
        $path = "app/pdfs/event" . $event->getKey() . ($subpath ? '/' . $subpath : '');
        return storage_path($path);
    }

    public static function modelFactory($type, $typeId)
    {
        $model = null;
        switch ($type) {
            case 'Country':
                $model = Country::find($typeId);
                break;
            case 'Role':
                if ($typeId == 0) {
                    $model = new Role();
                    $model->role_name = "Athlete";
                    $model->role_id = 0;
                }
                else {
                    $model = Role::find($typeId);
                }
                break;
            case 'Template':
                $model = AccreditationTemplate::find($typeId);
                break;
            case 'Event':
                $model = SideEvent::find($typeId);
                break;
        }
        return $model;
    }

    public static function split(Event $event, string $type, AccreditationRelation $model)
    {
        $service = new SummarySplitService($event, $type, $model);
        return $service->handle();
    }

    public static function createSummary(Document $document, string $type, AccreditationRelation $model)
    {
        $service = new SummaryCreateService($document, $type, $model);
        return $service->handle();
    }

    public function makeFpdi()
    {
        \Log::debug("creating Fpdi");
        return new \setasign\Fpdi\Tcpdf\Fpdi();
    }
}
