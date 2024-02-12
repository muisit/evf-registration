<?php

namespace Tests\Unit\App\Support\PDF;

use App\Models\Accreditation;
use Tests\Support\Data\Accreditation as AccreditationData;
use App\Support\Services\PDF\QRCode;
use App\Support\Services\PDFGenerator;
use Tests\Unit\TestCase;

class QRCodeTest extends TestCase
{
    public function fixtures()
    {
        AccreditationData::create();
    }

    private function mockPDF($label = false)
    {
        $pdfstub = $this->createMock(\TCPDF::class);
        if ($label !== false) {
            $pdfstub
                ->expects($this->once())
                ->method('write2DBarcode')
                ->with(
                    $this->equalTo($label),
                    $this->equalTo('QRCODE,H'),
                    $this->equalTo(5.0),
                    $this->equalTo(2.5),
                    $this->equalTo(50.0),
                    $this->equalTo(25.0),
                    $this->anything(),
                    $this->equalTo('N')
                );
        }
        return $pdfstub;
    }

    private function mockGenerator($pdf = null)
    {
        $stub = $this->createStub(PDFGenerator::class);
        $stub->accreditation = Accreditation::find(AccreditationData::MFCAT1);
        $stub->pdf = $pdf ? $pdf : $this->mockPDF(false);
        return $stub;
    }

    public function testGenerate()
    {
        // the id is linked to the accreditation row for MFCAT1
        $generator = $this->mockGenerator($this->mockPDF("--1-1-1270578-NED-2-ORG-BELL-Pete--"));
        $obj = new QRCode($generator);
        $element = (object)[
            "style" => (object) [
                'color' => '#111111',
                'top' => 10,
                'left' => 20,
                'height' => 100,
                'width' => 200
            ],
            "link" => "--%i-%e-%a-%c-%v-%o-%l-%f--",
            "ratio" => 1.4,
            'side' => 'left',
        ];
        $obj->withData((object)[
            'country' => 'NED',
            'category' => '2',
            'organisation' => 'ORG',
            'lastname' => 'BELL',
            'firstname' => 'Pete'
        ])->generate($element);
    }
}
