<?php

namespace Tests\Unit\App\Support\PDF;

use App\Support\Services\PDF\CountryFlag;
use App\Support\Services\PDFGenerator;
use Tests\Unit\TestCase;
use Carbon\Carbon;

class CountryFlagTest extends TestCase
{
    private function imagePath()
    {
        $fileName = 'tests/Support/Files/Portrait_exorientation_0.jpg';
        return base_path($fileName);
    }

    private function mockGenerator($coords)
    {
        $stub = $this->createStub(PDFGenerator::class);
        $pdfstub = $this->createMock(\TCPDF::class);
        if ($coords !== false) {
            $pdfstub->expects($this->once())->method('setJPEGQuality')->with($this->equalTo(90));
            $pdfstub
                ->expects($this->once())
                ->method('Image')
                ->with(
                    $this->equalTo($this->imagePath()),
                    $this->equalTo($coords[0]),
                    $this->equalTo($coords[1]),
                    $this->equalTo($coords[2]),
                    $this->equalTo($coords[3]),
                    $this->equalTo(''),
                    $this->equalTo(''),
                    $this->equalTo(''),
                    $this->equalTo(true),
                    $this->equalTo(600), // dpi
                    $this->equalTo(''),
                    $this->equalTo(false),
                    $this->equalTo(false),
                    $this->equalTo(0), // border
                    $this->equalTo('CM'),
                    $this->equalTo(false),
                    $this->equalTo(false),
                    $this->equalTo(false),
                    $this->equalTo([])
                );
        }
        $stub->pdf = $pdfstub;
        return $stub;
    }

    public function testInsertImage()
    {
        $generator = $this->mockGenerator([5.0, 2.5, 35.0, 25.0]);
        $obj = new CountryFlag($generator);
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
        $data = (object)[
            'country_flag' => 'tests/Support/Files/Portrait_exorientation_0.jpg'
        ];
        $obj->withData($data)->generate($element);
    }

    public function testRequiresPath()
    {
        // indicate no coordinates: image is never placed
        $generator = $this->mockGenerator(false);
        $obj = new CountryFlag($generator);
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
        $data = (object)[
            'country_flag' => 'nosuchfile'
        ];
        $obj->withData($data)->generate($element);
    }
}
