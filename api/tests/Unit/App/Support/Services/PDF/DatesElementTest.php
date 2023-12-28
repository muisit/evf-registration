<?php

namespace Tests\Unit\App\Support\PDF;

use App\Support\Services\PDF\DatesElement;
use App\Support\Services\PDFGenerator;
use Tests\Unit\TestCase;
use Carbon\Carbon;

class DatesElementTest extends TestCase
{
    private function mockGenerator($dates)
    {
        $stub = $this->createStub(PDFGenerator::class);
        $pdfstub = $this->createMock(\TCPDF::class);
        $pdfstub->expects($this->once())->method('SetTextColorArray');
        $pdfstub->expects($this->once())->method('SetTextRenderingMode');
        $pdfstub->expects($this->exactly(5))->method('SetFont');
        $pdfstub->expects($this->any())->method('SetFontSize');
        $pdfstub->expects($this->any())->method('GetCharWidth')->willReturn('1.0');
        $pdfstub->expects($this->any())->method('GetCellHeight')->willReturn('1.2');
        $pdfstub->expects($this->exactly(count($dates)))->method('Cell');
        $stub->pdf = $pdfstub;
        return $stub;
    }

    public function testGenerate()
    {
        $dates = ["Date1", "Date2", "Date3"];
        $generator = $this->mockGenerator($dates);
        $obj = new DatesElement($generator);
        $element = (object)[
            "style" => (object) [
                'color' => '#111111',
                'top' => 10,
                'left' => 20,
                'height' => 100,
                'width' => 200
            ],
        ];
        $obj->withData((object)["dates" => $dates])->generate($element);
    }

    public function testOneDate()
    {
        $dates = ["Date1", "Date2", "Date3"];
        $generator = $this->mockGenerator([1]);
        $obj = new DatesElement($generator);
        $element = (object)[
            "style" => (object) [
                'color' => '#111111',
                'top' => 10,
                'left' => 20,
                'height' => 100,
                'width' => 200
            ],
            'onedateonly' => true
        ];
        $obj->withData((object)["dates" => $dates])->generate($element);
    }

    public function testOneDateFalse()
    {
        $dates = ["Date1", "Date2", "Date3"];
        $generator = $this->mockGenerator($dates);
        $obj = new DatesElement($generator);
        $element = (object)[
            "style" => (object) [
                'color' => '#111111',
                'top' => 10,
                'left' => 20,
                'height' => 100,
                'width' => 200
            ],
            'onedateonly' => false
        ];
        $obj->withData((object)["dates" => $dates])->generate($element);
    }

}
