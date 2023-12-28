<?php

namespace Tests\Unit\App\Support\PDF;

use App\Support\Services\PDF\TextElement;
use App\Support\Services\PDFGenerator;
use Tests\Unit\TestCase;
use Carbon\Carbon;

class TextElementTest extends TestCase
{
    private function neverPDF()
    {
        return $this->createMock(\TCPDF::class);
    }

    private function linesPDF($count)
    {
        $pdfstub = $this->neverPDF();
        $pdfstub->expects($this->once())->method('SetTextRenderingMode');
        $pdfstub->expects($this->any())->method('SetFont');
        $pdfstub->expects($this->any())->method('SetFontSize');
        $pdfstub->expects($this->any())->method('GetFontSize')->willReturn('6'); // in mm
        $pdfstub->expects($this->any())->method('GetCharWidth')->willReturn('0.5');
        $pdfstub->expects($this->any())->method('GetCellHeight')->willReturn('2');
        $pdfstub->expects($this->exactly($count))->method('Cell');
        return $pdfstub;
    }

    private function oncePDF($label, $align, $hascolor)
    {
        $pdfstub = $this->neverPDF();
        if ($hascolor) {
            $pdfstub->expects($this->once())->method('SetTextColorArray');
        }
        $pdfstub->expects($this->once())->method('SetTextRenderingMode');
        $pdfstub->expects($this->exactly(5))->method('SetFont');
        $pdfstub->expects($this->any())->method('SetFontSize');
        $pdfstub->expects($this->any())->method('GetCharWidth')->willReturn('1.0');
        $pdfstub->expects($this->any())->method('GetCellHeight')->willReturn('1.2');
        $pdfstub
            ->expects($this->once())
            ->method('Cell')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->equalTo($label),
                $this->equalTo(0),
                $this->equalTo(0),
                $this->equalTo($align), // alignment
                $this->equalTo(false),
                $this->equalTo(''),
                $this->equalTo(0),
                $this->equalTo(false),
                $this->equalTo('T'),
                $this->equalTo('T')
            );
        return $pdfstub;
    }

    private function mockGenerator($pdf)
    {
        $stub = $this->createStub(PDFGenerator::class);
        $stub->pdf = $pdf;
        return $stub;
    }

    public function testParse()
    {
        $generator = $this->mockGenerator($this->neverPDF());
        $obj = new TextElement($generator);
        $element = (object)[
            "style" => (object) [
                'fontSize' => 28,
                'fontFamily' => 'Courier',
                'textAlign' => 'center'
            ],
        ];
        $obj->parse($element);
        $options = $obj->options();
        $this->assertEquals('C', $options->alignment);
        $this->assertEquals(28, $options->fontSize);
        $this->assertEquals('Courier', $options->fontFamily);
        $this->assertEquals(true, $options->wrap);
        $this->assertEquals(false, $options->replaceTilde);

        $element->style->textAlign = 'justify';
        $element->style->fontFamily = 'nosuchfam';
        unset($element->style->fontSize);
        $obj->parse($element);
        $options = $obj->options();
        $this->assertEquals('J', $options->alignment);
        $this->assertEquals('Helvetica', $options->fontFamily);
        $this->assertEquals(20, $options->fontSize);

        $element->style->textAlign = 'right';
        $element->style->fontFamily = 'FreeSans';
        $obj->parse($element);
        $options = $obj->options();
        $this->assertEquals('FreeSans', $options->fontFamily);
        $this->assertEquals('R', $options->alignment);

        $element->style->textAlign = 'left'; // or any other value
        unset($element->style->fontFamily);
        $obj->parse($element);
        $options = $obj->options();
        $this->assertEquals('', $options->alignment);
        $this->assertEquals('Helvetica', $options->fontFamily);

        $element->style->textAlign = 'right'; // make sure the element has a non '' alignment now
        $obj->parse($element);
        unset($element->style->textAlign); // then reset it
        $obj->parse($element);
        $options = $obj->options();
        $this->assertEquals('', $options->alignment);
    }

    public function testGenerateLine()
    {
        $line = "A simple text";
        $generator = $this->mockGenerator($this->oncePDF($line, '', true));
        $obj = new TextElement($generator);
        $element = (object)[
            'style' => (object)[
                'color' => '#aaa'
            ],
            'text' => $line
        ];
        $obj->generate($element);
    }

    public function testGenerateCenterLine()
    {
        $line = "A simple text";
        $generator = $this->mockGenerator($this->oncePDF($line, 'C', false));
        $obj = new TextElement($generator);
        $element = (object)[
            "style" => (object) [
                'fontSize' => 28,
                'fontFamily' => 'Courier',
                'textAlign' => 'center'
            ],
            'text' => $line
        ];
        $obj->generate($element);
    }

    public function testGenerateLines()
    {
        $line = "A simple text but too wide for the box, so it is being wrapped";
        $generator = $this->mockGenerator($this->linesPDF(2));
        $obj = new TextElement($generator);
        $element = (object)[
            "style" => (object) [
                'fontSize' => 28,
                'fontFamily' => 'Courier',
                'textAlign' => 'center',
                'width' => 100, // max width is 105mm
                'height' => 120 // max height is 148mm
            ],
            'text' => $line
        ];
        $obj->generate($element);
    }

    public function testGenerateLines2()
    {
        $line = "A simple text but too wide for the box, so it is being wrapped";
        $generator = $this->mockGenerator($this->linesPDF(7));
        $obj = new TextElement($generator);
        $element = (object)[
            "style" => (object) [
                'fontSize' => 28,
                'fontFamily' => 'Courier',
                'textAlign' => 'center',
                'width' => 20,
                'height' => 140
            ],
            'text' => $line
        ];
        $obj->generate($element);
    }

    public function testGenerateTilde()
    {
        $line = "A simple~text but too~wide for the box, so~it~is being~wrapped";
        $generator = $this->mockGenerator($this->linesPDF(8));
        $obj = new TextElement($generator);
        $obj->replaceTildeForSpaces();
        $element = (object)[
            "style" => (object) [
                'fontSize' => 28,
                'fontFamily' => 'Courier',
                'textAlign' => 'center',
                'width' => 20,
                'height' => 140
            ],
            'text' => $line
        ];
        $obj->generate($element);
    }

    public function testBoxHeight()
    {
        $line = "A simple text but too wide for the box, so it is being wrapped";
        $generator = $this->mockGenerator($this->linesPDF(3)); // 7 lines, 4 are dumped
        $obj = new TextElement($generator);
        $obj->replaceTildeForSpaces();
        $element = (object)[
            "style" => (object) [
                'fontSize' => 28,
                'fontFamily' => 'Courier',
                'textAlign' => 'center',
                'width' => 20,
                'height' => 19
            ],
            'text' => $line
        ];
        $obj->generate($element);
    }
}
