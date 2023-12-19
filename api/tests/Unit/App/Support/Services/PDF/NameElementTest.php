<?php

namespace Tests\Unit\App\Support\PDF;

use App\Support\Services\PDF\NameElement;
use App\Support\Services\PDFGenerator;
use Tests\Unit\TestCase;

class NameElementTest extends TestCase
{
    private function mockGenerator($label)
    {
        $stub = $this->createStub(PDFGenerator::class);
        $pdfstub = $this->createMock(\TCPDF::class);
        $pdfstub->expects($this->once())->method('SetTextColorArray');
        $pdfstub->expects($this->once())->method('SetTextRenderingMode');
        $pdfstub->expects($this->exactly(5))->method('SetFont');
        $pdfstub->expects($this->any())->method('SetFontSize');
        $pdfstub->expects($this->any())->method('GetCharWidth')->willReturn('1.0');
        $pdfstub->expects($this->any())->method('GetCellHeight')->willReturn('1.2');
        $pdfstub->expects($this->once())
            ->method('Cell')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->equalTo($label),
                $this->equalTo(0),
                $this->equalTo(0),
                $this->equalTo(''),
                $this->equalTo(false),
                $this->equalTo(''),
                $this->equalTo(0),
                $this->equalTo(false),
                $this->equalTo('T'),
                $this->equalTo('T')
            );
        $stub->pdf = $pdfstub;
        return $stub;
    }

    public function testGenerate()
    {
        $generator = $this->mockGenerator("Bell, Pete");
        $obj = new NameElement($generator);
        $element = (object)[
            "style" => (object) [
                'color' => '#111111',
                'top' => 10,
                'left' => 20,
                'height' => 100,
                'width' => 200
            ],
        ];
        $obj->withData((object)["firstname" => "Pete", "lastname" => "Bell"])->generate($element);
    }

    public function testGenerateFirst()
    {
        $generator = $this->mockGenerator("Pete");
        $obj = new NameElement($generator);
        $element = (object)[
            "style" => (object) [
                'color' => '#111111',
                'top' => 10,
                'left' => 20,
                'height' => 100,
                'width' => 200
            ],
            'name' => 'first'
        ];
        $obj->withData((object)["firstname" => "Pete", "lastname" => "Bell"])->generate($element);
    }

    public function testGenerateLast()
    {
        $generator = $this->mockGenerator("Bell");
        $obj = new NameElement($generator);
        $element = (object)[
            "style" => (object) [
                'color' => '#111111',
                'top' => 10,
                'left' => 20,
                'height' => 100,
                'width' => 200
            ],
            'name' => 'last'
        ];
        $obj->withData((object)["firstname" => "Pete", "lastname" => "Bell"])->generate($element);
    }

    public function testGenerateOther()
    {
        $generator = $this->mockGenerator("Bell, Pete");
        $obj = new NameElement($generator);
        $element = (object)[
            "style" => (object) [
                'color' => '#111111',
                'top' => 10,
                'left' => 20,
                'height' => 100,
                'width' => 200
            ],
            'name' => 'other'
        ];
        $obj->withData((object)["firstname" => "Pete", "lastname" => "Bell"])->generate($element);
    }
}
