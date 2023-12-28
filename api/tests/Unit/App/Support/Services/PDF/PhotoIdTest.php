<?php

namespace Tests\Unit\App\Support\PDF;

use App\Models\Accreditation;
use Tests\Support\Data\Accreditation as AccreditationData;
use App\Support\Services\PDF\PhotoId;
use App\Support\Services\PDFGenerator;
use Tests\Unit\TestCase;

class PhotoIdTest extends TestCase
{
    public function fixtures()
    {
        AccreditationData::create();
    }

    private function imagePath()
    {
        $fileName = 'tests/Support/Files/Portrait_exorientation_0.jpg';
        return base_path($fileName);
    }

    private function mockGenerator($coords)
    {
        $stub = $this->createStub(PDFGenerator::class);
        $stub->accreditation = Accreditation::find(AccreditationData::MFCAT1);
        $pdfstub = $this->createMock(\TCPDF::class);
        if ($coords !== false) {
            $pdfstub->expects($this->once())->method('setJPEGQuality')->with($this->equalTo(90));
            $pdfstub
                ->expects($this->once())
                ->method('Image')
                ->with(
                    $this->anything(),
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
        $generator = $this->mockGenerator([5.0, 2.5, 19.444444444444446, 25.0]);
        $obj = new PhotoId($generator);
        $element = (object)[
            "style" => (object) [
                'color' => '#111111',
                'top' => 10,
                'left' => 20,
                'height' => 100,
                'width' => 200
            ],
            "test" => $this->imagePath(), // allow testing without requiring a fencer with a photo
            "ratio" => 1.1,
            'side' => 'left',
        ];
        $obj->generate($element);
    }

    public function testRequiresImage()
    {
        $generator = $this->mockGenerator(false);
        $obj = new PhotoId($generator);
        $element = (object)[
            "style" => (object) [
                'color' => '#111111',
                'top' => 10,
                'left' => 20,
                'height' => 100,
                'width' => 200
            ],
            "file_id" => "afile",
            "ratio" => 1.4,
            'side' => 'left',
        ];
        $obj->generate($element);
    }
}
