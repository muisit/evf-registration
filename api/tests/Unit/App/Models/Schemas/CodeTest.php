<?php

namespace Tests\Unit\App\Models\Schemas;

use App\Models\Schemas\Code;
use Tests\Unit\TestCase;

class CodeTest extends TestCase
{
    public function testValidate()
    {
        $code = new Code();
        $code->original = '99058223190001';
        $code->baseFunction = 9;
        $code->addFunction = 0;
        $code->id1 = 582;
        $code->id2 = 231;
        $code->validation = 9;
        $code->payload = '0001';

        $this->assertTrue($code->validate());

        $code->baseFunction = 0;
        $this->assertFalse($code->validate());
        $code->baseFunction = 10;
        $this->assertFalse($code->validate());
        $code->baseFunction = 8;
        $this->assertFalse($code->validate());
        $code->original = '88058223190001';
        $this->assertTrue($code->validate()); // baseFunction is not part of the checksum
        $code->baseFunction = 9;
        $code->original = '99058223190001';
        $this->assertTrue($code->validate());

        $code->addFunction = -1;
        $this->assertFalse($code->validate());
        $code->addFunction = 10;
        $this->assertFalse($code->validate());
        $code->addFunction = 1;
        $this->assertFalse($code->validate());
        $code->original = '99158223190001';
        $this->assertFalse($code->validate()); // checksum fails
        $code->validation = 8;
        $this->assertFalse($code->validate()); // check with original fails
        $code->original = '99158223180001';
        $this->assertTrue($code->validate());

        $code->payload = '-1';
        $this->assertFalse($code->validate());
        $code->payload = 'aaa';
        $this->assertFalse($code->validate());
        $code->payload = '10001';
        $this->assertFalse($code->validate());
        $code->payload = '0001';
        $this->assertTrue($code->validate());
    }
}
