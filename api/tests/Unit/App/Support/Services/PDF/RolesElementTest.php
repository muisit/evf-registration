<?php

namespace Tests\Unit\App\Support\PDF;

use App\Support\Services\PDF\RolesElement;
use App\Support\Services\PDFGenerator;
use Tests\Unit\TestCase;
use Carbon\Carbon;

class RolesElementTest extends TestCase
{
    private function mockGenerator($renders = true)
    {
        $stub = $this->createStub(PDFGenerator::class);
        $pdfstub = $this->createMock(\TCPDF::class);
        if ($renders) {
            $pdfstub->expects($this->once())->method('SetTextColorArray');
            $pdfstub->expects($this->once())->method('SetTextRenderingMode');
            $pdfstub->expects($this->exactly(5))->method('SetFont');
            $pdfstub->expects($this->any())->method('SetFontSize');
            $pdfstub->expects($this->any())->method('GetCharWidth')->willReturn('1.0');
            $pdfstub->expects($this->any())->method('GetCellHeight')->willReturn('1.2');
            $pdfstub->expects($this->once())->method('Cell');
        }
        $stub->pdf = $pdfstub;
        return $stub;
    }

    public function testGenerate()
    {
        $roles = ["Role1", "Role2", "Role3"];
        $generator = $this->mockGenerator();
        $obj = new RolesElement($generator);
        $element = (object)[
            "style" => (object) [
                'color' => '#111111',
                'top' => 10,
                'left' => 20,
                'height' => 100,
                'width' => 200
            ],
        ];
        $obj->withData((object)["roles" => $roles])->generate($element);
    }

    public function testFail()
    {
        $roles = null;
        $generator = $this->mockGenerator(false);
        $obj = new RolesElement($generator);
        $element = (object)[
            "style" => (object) [
                'color' => '#111111',
                'top' => 10,
                'left' => 20,
                'height' => 100,
                'width' => 200
            ],
        ];
        $obj->withData((object)["roles" => $roles])->generate($element);
    }
}
