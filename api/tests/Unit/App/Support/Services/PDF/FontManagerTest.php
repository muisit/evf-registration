<?php

namespace Tests\Unit\App\Support\PDF;

use App\Support\Services\PDF\FontManager;
use App\Support\Services\PDFGenerator;
use Tests\Unit\TestCase;
use Carbon\Carbon;

class FontManagerTest extends TestCase
{
    private function mockGenerator($doesadd = true, $doesset = true)
    {
        $stub = $this->createStub(PDFGenerator::class);
        $pdfstub = $this->createMock(\TCPDF::class);
        if ($doesadd) $pdfstub->expects($this->once())->method('AddFont');
        if ($doesset) $pdfstub->expects($this->once())->method('SetFont');
        $stub->pdf = $pdfstub;
        return $stub;
    }

    public function testGenerate()
    {
        (new FontManager($this->mockGenerator()))->add("DejaVuSans Mono Italic");
    }

    public function testHelvetica()
    {
        (new FontManager($this->mockGenerator(false)))->add("Helvetica");
    }

    public function testCourier()
    {
        (new FontManager($this->mockGenerator(false)))->add("Courier Bold");
    }

    public function testNotExisting()
    {
        (new FontManager($this->mockGenerator(false, false)))->add("unknownfont");
    }

    public function testEuroFurence()
    {
        (new FontManager($this->mockGenerator(false, false)))->add("eurofurenceI");
    }
}
