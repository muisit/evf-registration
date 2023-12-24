<?php

namespace Tests\Unit\App\Support\PDF;

use App\Models\Accreditation;
use App\Models\Country;
use App\Models\Document;
use App\Models\Event;
use App\Support\Enums\PagePositions;
use Tests\Support\Data\Accreditation as AccreditationData;
use Tests\Support\Data\Event as EventData;
use App\Support\Services\PDF\SummaryCreateService;
use App\Support\Services\PDFService;
use Tests\Unit\TestCase;
use Laravel\Lumen\Application;

class SummaryCreateServiceTest extends TestCase
{
    public function fixtures()
    {
        EventData::create();
        AccreditationData::create();
    }

    private function mockPDF()
    {
        $a6template = [
            0 => 210,
            1 => 148.5,
            'width' => 210,
            'height' => 148.5,
            'orientation' => 'L'
        ];
        $pdf = $this->createMock(\setasign\Fpdi\Tcpdf\Fpdi::class);
        $pdf->expects($this->exactly(3))->method('AddPage');
        $pdf->expects($this->exactly(5))->method('useImportedPage');
        $pdf->expects($this->exactly(3))->method('getTemplateSize')->willReturn($a6template);
        $pdf->expects($this->once())->method('Output')->with($this->equalTo(storage_path('app/pdfs/event1/documents/summary_doc.pdf')), $this->equalTo('F'));
        return $pdf;
    }

    public function testHandle()
    {
        $path = 'summary_doc.pdf';
        $mockService = $this->createMock(PDFService::class);
        $mockService
            ->expects($this->once())
            ->method('makeFpdi')
            ->willReturn($this->mockPDF());

        app()->bind(PDFService::class, function (Application $app) use ($mockService) {
            return $mockService;
        });

        $country = Country::find(Country::GER);
        $accreditations = $country->selectAccreditations(Event::find(EventData::EVENT1));
        $accreditations->map(function (Accreditation $accreditation) {
            $path = $accreditation->path();
            file_put_contents($path, "test");
        });

        $document = new Document();
        $document->event_id = EventData::EVENT1;
        $document->type = 'Country';
        $document->type_id = Country::GER;
        $document->setConfig(["accreditations" => $accreditations->pluck('id')]);
        $document->path = $path;
        $document->save();

        $service = new SummaryCreateService($document);
        $service->handle();

        $accreditations->map(function (Accreditation $accreditation) {
            $path = $accreditation->path();
            @unlink($path);
        });
    }

    public function _testPositionPage()
    {
        $country = Country::find(Country::GER);
        $accreditations = $country->selectAccreditations(Event::find(EventData::EVENT1));
        $document = new Document();
        $document->event_id = EventData::EVENT1;
        $document->type = 'Country';
        $document->type_id = Country::GER;
        $document->setConfig(["accreditations" => $accreditations->pluck('id')]);
        $document->path = 'summary_doc.pdf';
        $document->save();

        $service = new SummaryCreateService($document);

        $this->assertEquals([PagePositions::A4_1, PagePositions::A4_3], $service->positionPage("a4portrait", null));
        $this->assertEquals([null, null], $service->positionPage("a4portrait", PagePositions::A4_1));
        $this->assertEquals([null, null], $service->positionPage("a4portrait", PagePositions::A4_2));
        $this->assertEquals([PagePositions::A4_3, null], $service->positionPage("a4portrait", PagePositions::A4_3));
        $this->assertEquals([null, null], $service->positionPage("a4portrait", PagePositions::A4_4));
        $this->assertEquals([null, null], $service->positionPage("a4portrait", PagePositions::A4L_1));
        $this->assertEquals([null, null], $service->positionPage("a4portrait", PagePositions::A4L_2));
        $this->assertEquals([null, null], $service->positionPage("a4portrait", PagePositions::A5L_1));
        $this->assertEquals([null, null], $service->positionPage("a4portrait", PagePositions::A5L_2));
        $this->assertEquals([null, null], $service->positionPage("a4portrait", PagePositions::A6));

        $this->assertEquals([PagePositions::A4L_1, null], $service->positionPage("a4landscape", null));
        $this->assertEquals([PagePositions::A4L_1, null], $service->positionPage("a4landscape", PagePositions::A4_1));
        $this->assertEquals([PagePositions::A4L_1, null], $service->positionPage("a4landscape", PagePositions::A4_2));
        $this->assertEquals([PagePositions::A4L_1, null], $service->positionPage("a4landscape", PagePositions::A4_3));
        $this->assertEquals([PagePositions::A4L_1, null], $service->positionPage("a4landscape", PagePositions::A4_4));
        $this->assertEquals([PagePositions::A4L_1, null], $service->positionPage("a4landscape", PagePositions::A4L_1));
        $this->assertEquals([PagePositions::A4L_1, null], $service->positionPage("a4landscape", PagePositions::A4L_2));
        $this->assertEquals([PagePositions::A4L_1, null], $service->positionPage("a4landscape", PagePositions::A5L_1));
        $this->assertEquals([PagePositions::A4L_1, null], $service->positionPage("a4landscape", PagePositions::A5L_2));
        $this->assertEquals([PagePositions::A4L_1, null], $service->positionPage("a4landscape", PagePositions::A6));

        $this->assertEquals([PagePositions::A4_1, PagePositions::A4_2], $service->positionPage("a4portrait2", null));
        $this->assertEquals([null, null], $service->positionPage("a4portrait2", PagePositions::A4_1));
        $this->assertEquals([PagePositions::A4_2, PagePositions::A4_3], $service->positionPage("a4portrait2", PagePositions::A4_2));
        $this->assertEquals([PagePositions::A4_3, PagePositions::A4_4], $service->positionPage("a4portrait2", PagePositions::A4_3));
        $this->assertEquals([PagePositions::A4_4, null], $service->positionPage("a4portrait2", PagePositions::A4_4));
        $this->assertEquals([null, null], $service->positionPage("a4portrait2", PagePositions::A4L_1));
        $this->assertEquals([null, null], $service->positionPage("a4portrait2", PagePositions::A4L_2));
        $this->assertEquals([null, null], $service->positionPage("a4portrait2", PagePositions::A5L_1));
        $this->assertEquals([null, null], $service->positionPage("a4portrait2", PagePositions::A5L_2));
        $this->assertEquals([null, null], $service->positionPage("a4portrait2", PagePositions::A6));

        $this->assertEquals([PagePositions::A4L_1, PagePositions::A4L_2], $service->positionPage("a4landscape2", null));
        $this->assertEquals([null, null], $service->positionPage("a4landscape2", PagePositions::A4_1));
        $this->assertEquals([null, null], $service->positionPage("a4landscape2", PagePositions::A4_2));
        $this->assertEquals([null, null], $service->positionPage("a4landscape2", PagePositions::A4_3));
        $this->assertEquals([null, null], $service->positionPage("a4landscape2", PagePositions::A4_4));
        $this->assertEquals([null, null], $service->positionPage("a4landscape2", PagePositions::A4L_1));
        $this->assertEquals([PagePositions::A4L_2, null], $service->positionPage("a4landscape2", PagePositions::A4L_2));
        $this->assertEquals([null, null], $service->positionPage("a4landscape2", PagePositions::A5L_1));
        $this->assertEquals([null, null], $service->positionPage("a4landscape2", PagePositions::A5L_2));
        $this->assertEquals([null, null], $service->positionPage("a4landscape2", PagePositions::A6));

        $this->assertEquals([PagePositions::A5L_1, null], $service->positionPage("a5landscape", null));
        $this->assertEquals([PagePositions::A5L_1, null], $service->positionPage("a5landscape", PagePositions::A4_1));
        $this->assertEquals([PagePositions::A5L_1, null], $service->positionPage("a5landscape", PagePositions::A4_2));
        $this->assertEquals([PagePositions::A5L_1, null], $service->positionPage("a5landscape", PagePositions::A4_3));
        $this->assertEquals([PagePositions::A5L_1, null], $service->positionPage("a5landscape", PagePositions::A4_4));
        $this->assertEquals([PagePositions::A5L_1, null], $service->positionPage("a5landscape", PagePositions::A4L_1));
        $this->assertEquals([PagePositions::A5L_1, null], $service->positionPage("a5landscape", PagePositions::A4L_2));
        $this->assertEquals([PagePositions::A5L_1, null], $service->positionPage("a5landscape", PagePositions::A5L_1));
        $this->assertEquals([PagePositions::A5L_1, null], $service->positionPage("a5landscape", PagePositions::A5L_2));
        $this->assertEquals([PagePositions::A5L_1, null], $service->positionPage("a5landscape", PagePositions::A6));

        $this->assertEquals([PagePositions::A5L_1, PagePositions::A5L_2], $service->positionPage("a5landscape2", null));
        $this->assertEquals([null, null], $service->positionPage("a5landscape2", PagePositions::A4_1));
        $this->assertEquals([null, null], $service->positionPage("a5landscape2", PagePositions::A4_2));
        $this->assertEquals([null, null], $service->positionPage("a5landscape2", PagePositions::A4_3));
        $this->assertEquals([null, null], $service->positionPage("a5landscape2", PagePositions::A4_4));
        $this->assertEquals([null, null], $service->positionPage("a5landscape2", PagePositions::A4L_1));
        $this->assertEquals([null, null], $service->positionPage("a5landscape2", PagePositions::A4L_2));
        $this->assertEquals([null, null], $service->positionPage("a5landscape2", PagePositions::A5L_1));
        $this->assertEquals([PagePositions::A5L_2, null], $service->positionPage("a5landscape2", PagePositions::A5L_2));
        $this->assertEquals([null, null], $service->positionPage("a5landscape2", PagePositions::A6));

        $this->assertEquals([PagePositions::A6, null], $service->positionPage("a6portrait", null));
        $this->assertEquals([PagePositions::A6, null], $service->positionPage("a6portrait", PagePositions::A4_1));
        $this->assertEquals([PagePositions::A6, null], $service->positionPage("a6portrait", PagePositions::A4_2));
        $this->assertEquals([PagePositions::A6, null], $service->positionPage("a6portrait", PagePositions::A4_3));
        $this->assertEquals([PagePositions::A6, null], $service->positionPage("a6portrait", PagePositions::A4_4));
        $this->assertEquals([PagePositions::A6, null], $service->positionPage("a6portrait", PagePositions::A4L_1));
        $this->assertEquals([PagePositions::A6, null], $service->positionPage("a6portrait", PagePositions::A4L_2));
        $this->assertEquals([PagePositions::A6, null], $service->positionPage("a6portrait", PagePositions::A5L_1));
        $this->assertEquals([PagePositions::A6, null], $service->positionPage("a6portrait", PagePositions::A5L_2));
        $this->assertEquals([PagePositions::A6, null], $service->positionPage("a6portrait", PagePositions::A6));
    }

    public function _testPlacePage()
    {
        $country = Country::find(Country::GER);
        $accreditations = $country->selectAccreditations(Event::find(EventData::EVENT1));
        $document = new Document();
        $document->event_id = EventData::EVENT1;
        $document->type = 'Country';
        $document->type_id = Country::GER;
        $document->setConfig(["accreditations" => $accreditations->pluck('id')]);
        $document->path = 'summary_doc.pdf';
        $document->save();

        $service = new SummaryCreateService($document);
        $this->assertEquals([0, 0, 210, 297], $service->placePage(PagePositions::A4_1));
        $this->assertEquals([105, 0, 210, 297], $service->placePage(PagePositions::A4_2));
        $this->assertEquals([0, 148.5, 210, 297], $service->placePage(PagePositions::A4_3));
        $this->assertEquals([105, 148.5, 210, 297], $service->placePage(PagePositions::A4_4));
        $this->assertEquals([43, 31, 297, 210], $service->placePage(PagePositions::A4L_1));
        $this->assertEquals([148, 31, 297, 210], $service->placePage(PagePositions::A4L_2));
        $this->assertEquals([0, 0, 210, 148.5], $service->placePage(PagePositions::A5L_1));
        $this->assertEquals([105, 0, 210, 148.5], $service->placePage(PagePositions::A5L_2));
        $this->assertEquals([0, 0, 105, 148.5], $service->placePage(PagePositions::A6));
    }
}
