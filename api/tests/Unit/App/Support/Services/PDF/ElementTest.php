<?php

namespace Tests\Unit\App\Support\PDF;

use App\Support\Services\PDF\Element;
use App\Support\Services\PDFGenerator;
use Tests\Unit\TestCase;
use Carbon\Carbon;

class ElementTest extends TestCase
{
    public function testGenerate()
    {
        $obj = new Element($this->createStub(PDFGenerator::class));
        $element = (object)[
            "style" => (object) [
                'color' => '#111111',
                'top' => 10,
                'left' => 20,
                'height' => 100,
                'width' => 200
            ],
        ];
        $obj->parse($element);
        $options = $obj->options();
        $this->assertEquals([5.0, 2.5], $options->offset);
        $this->assertEquals([50.0, 25.0], $options->size);
        $this->assertEquals([17, 17, 17], $options->colour);
        $this->assertEquals(1.0, $options->ratio);

        $element->style->color = '#888';
        $obj->parse($element);
        $options = $obj->options();
        $this->assertEquals([136, 136, 136], $options->colour);
    }

    public function testRatio()
    {
        $obj = new Element($this->createStub(PDFGenerator::class));
        $element = (object)[
            "style" => (object) [
                'color' => '#111111',
                'top' => 10,
                'left' => 20,
                'height' => 100,
                'width' => 0
            ],
        ];

        $element->ratio = 0.1;
        $obj->parse($element);
        $options = $obj->options();
        $this->assertEquals([5.0, 2.5], $options->offset);
        $this->assertEquals([2.5, 25.0], $options->size);
        $this->assertEquals(0.1, $options->ratio);

        $element->style->width = 200;
        $element->style->height = 0;
        $obj->parse($element);
        $options = $obj->options();
        $this->assertEquals([50.0, 500.0], $options->size);
    }
}
