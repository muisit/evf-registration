<?php

namespace Tests\Unit\App\Support\PDF;

use App\Support\Services\PDF\Box;
use App\Support\Services\PDFGenerator;
use Tests\Unit\TestCase;
use Carbon\Carbon;

class BoxTest extends TestCase
{
    private function mockGenerator($coords)
    {
        $stub = $this->createStub(PDFGenerator::class);
        $pdfstub = $this->createMock(\TCPDF::class);
        if ($coords !== false) {
            $pdfstub
                ->expects($this->once())
                ->method('Rect')
                ->with(
                    $this->equalTo($coords[0]),
                    $this->equalTo($coords[1]),
                    $this->equalTo($coords[2]),
                    $this->equalTo($coords[3]),
                    $this->equalTo('F'),
                    $this->equalTo(['all' => 0]),
                    $this->equalTo($coords[4])
                );
        }
        $stub->pdf = $pdfstub;
        return $stub;
    }

    public function testBasicBox()
    {
        $generator = $this->mockGenerator([5.0, 2.5, 50.0, 25.0, [17, 17, 17]]);
        $obj = new Box($generator);
        $element = (object)[
            "style" => (object) [
                'color' => '#111111',
                'top' => 10,
                'left' => 20,
                'height' => 100,
                'width' => 200
            ],
        ];
        $obj->generate($element);
    }
}
