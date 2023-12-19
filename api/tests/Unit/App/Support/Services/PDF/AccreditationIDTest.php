<?php

namespace Tests\Unit\App\Support\PDF;

use App\Support\Services\PDF\AccreditationID;
use App\Support\Services\PDFGenerator;
use Tests\Unit\TestCase;
use Carbon\Carbon;

class AccreditationIDTest extends TestCase
{
    private function mockPDF($withCall = false)
    {
        $pdfstub = $this->createMock(\TCPDF::class);
        if ($withCall) {
            $pdfstub
                ->expects($this->once())
                ->method('write2DBarcode')
                ->with(
                    $this->equalTo('mylabel'),
                    $this->equalTo('QRCODE,H'),
                    $this->equalTo(5.0),
                    $this->equalTo(2.5),
                    $this->equalTo(50.0),
                    $this->equalTo(25.0),
                    $this->anything(),
                    $this->equalTo('N')
                );

            $pdfstub->expects($this->once())->method('SetTextColorArray');
            $pdfstub->expects($this->once())->method('SetTextRenderingMode');
            $pdfstub->expects($this->any())->method('SetFont');
            $pdfstub->expects($this->any())->method('SetFontSize');
            $pdfstub->expects($this->any())->method('GetCharWidth')->willReturn('1.0');
            $pdfstub->expects($this->any())->method('GetCellHeight')->willReturn('1.2');
            $pdfstub->expects($this->any())->method('Cell');
        }
        return $pdfstub;
    }

    private function mockGenerator($pdf = null)
    {
        $stub = $this->createStub(PDFGenerator::class);
        $stub->pdf = $pdf ? $pdf : $this->mockPDF();
        return $stub;
    }

    public function testGenerate()
    {
        $generator = $this->mockGenerator();
        $obj = new AccreditationID($generator);
        $element = (object)[
            "style" => (object) [
                'color' => '#111111',
                'top' => 10,
                'left' => 20,
                'height' => 100,
                'width' => 200
            ],
            "notparsed" => true,
            "ratio" => 1.4,
            'side' => 'left',
        ];
        $obj->withLabel('mylabel')->generate($element);
        $options = $obj->options();
        $this->assertEquals([5.0, 2.5], $options->offset);
        $this->assertEquals([50.0, 25.0], $options->size);
        $this->assertEquals(1.4, $options->ratio);
        $this->assertEquals([17, 17, 17], $options->colour);
        $this->assertEquals('left', $options->side);
        $this->assertEquals('mylabel', $options->label);
        $this->assertEquals($obj, $generator->accreditationId);

        // defaults for options
        $obj = new AccreditationID($generator);
        $obj->generate((object)[], null);
        $options = $obj->options();
        $this->assertEquals([0,0], $options->offset);
        $this->assertEquals([0,0], $options->size);
        $this->assertEquals(1.0, $options->ratio);
        $this->assertEquals('#000000', $options->colour);
        $this->assertEquals('both', $options->side);
        $this->assertEquals('', $options->label);
    }

    public function testFinalise()
    {
        $generator = $this->mockGenerator($this->mockPDF(true));
        $obj = new AccreditationID($generator);
        $element = (object)[
            "style" => (object) [
                'color' => '#111111',
                'top' => 10,
                'left' => 20,
                'height' => 100,
                'width' => 200
            ],
            "notparsed" => true,
            "ratio" => 1.4,
            'side' => 'left',
        ];
        $obj->withLabel('mylabel')->generate($element);
        $obj->finalise();
    }
}
