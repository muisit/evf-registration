<?php

namespace Tests\Unit\App\Support;

use App\Support\Services\PhotoAssessService;
use Tests\Unit\TestCase;

class PhotoAssessServiceTest extends TestCase
{
    public function convertFile($fileName)
    {
        $tempFile = tempnam('/tmp', 'evftest');
        @copy(base_path($fileName), $tempFile);
        $this->assertTrue(file_exists($tempFile));
        $newFile = PhotoAssessService::convert($tempFile, 'image/jpg');
        $this->assertNotEmpty($newFile);
        $this->assertEquals($newFile, $tempFile);
        return $newFile;
    }

    public function testConvert0()
    {
        $newFile = $this->convertFile('tests/Support/Files/Portrait_exorientation_0.jpg');
        $this->assertTrue(file_exists($newFile));
        $this->assertEquals('3d291deabf7fb443c25f549abeca391f', hash_file('md5', $newFile));
        @unlink($newFile);
    }

    public function testConvert1()
    {
        $newFile = $this->convertFile('tests/Support/Files/Portrait_exorientation_1.jpg');
        $this->assertTrue(file_exists($newFile));
        $this->assertEquals('84048365f117749f0122718cee1ef61e', hash_file('md5', $newFile));
        @unlink($newFile);
    }

    public function testConvert2()
    {
        $newFile = $this->convertFile('tests/Support/Files/Portrait_exorientation_2.jpg');
        $this->assertTrue(file_exists($newFile));
        $this->assertEquals('edaa7db631ed0288fcdeeaca1fcd2e37', hash_file('md5', $newFile));
        @unlink($newFile);
    }

    public function testConvert3()
    {
        $newFile = $this->convertFile('tests/Support/Files/Portrait_exorientation_3.jpg');
        $this->assertTrue(file_exists($newFile));
        $this->assertEquals('dcd0765eff297d1f1b9b5c08e60af111', hash_file('md5', $newFile));
        @unlink($newFile);
    }

    public function testConvert4()
    {
        $newFile = $this->convertFile('tests/Support/Files/Portrait_exorientation_4.jpg');
        $this->assertTrue(file_exists($newFile));
        $this->assertEquals('588ed13c307f710cc49fca56c272423a', hash_file('md5', $newFile));
        @unlink($newFile);
    }

    public function testConvert5()
    {
        $newFile = $this->convertFile('tests/Support/Files/Portrait_exorientation_5.jpg');
        $this->assertTrue(file_exists($newFile));
        $this->assertEquals('5207eba5c1ab3964a8428b7172e80d01', hash_file('md5', $newFile));
        @unlink($newFile);
    }

    public function testConvert6()
    {
        $newFile = $this->convertFile('tests/Support/Files/Portrait_exorientation_6.jpg');
        $this->assertTrue(file_exists($newFile));
        $this->assertEquals('daf7397269779e63bdba7dbbd49bd3fb', hash_file('md5', $newFile));
        @unlink($newFile);
    }

    public function testConvert7()
    {
        $newFile = $this->convertFile('tests/Support/Files/Portrait_exorientation_7.jpg');
        $this->assertTrue(file_exists($newFile));
        $this->assertEquals('e512025acd521417ad948c1908b5afc6', hash_file('md5', $newFile));
        @unlink($newFile);
    }

    public function testConvert8()
    {
        $newFile = $this->convertFile('tests/Support/Files/Portrait_exorientation_8.jpg');
        $this->assertTrue(file_exists($newFile));
        $this->assertEquals('a2da8dc85ecb9b95ff2561f5ec6a9a5b', hash_file('md5', $newFile));
        @unlink($newFile);
    }

    public function testConvertFailMime()
    {
        $fileName = 'tests/Support/Files/Portrait_exorientation_0.jpg';
        $tempFile = tempnam('/tmp', 'evftest');
        @copy(base_path($fileName), $tempFile);
        $this->assertTrue(file_exists($tempFile));
        $newFile = PhotoAssessService::convert($tempFile, 'image/bmp');
        $this->assertEmpty($newFile);
        $newFile = PhotoAssessService::convert($tempFile, 'image/avi');
        $this->assertEmpty($newFile);
        $newFile = PhotoAssessService::convert($tempFile, 'application/pdf');
        $this->assertEmpty($newFile);
        $newFile = PhotoAssessService::convert($tempFile, 'text/plain');
        $this->assertEmpty($newFile);
        $newFile = PhotoAssessService::convert($tempFile, null);
        $this->assertEmpty($newFile);
        @unlink($tempFile);
    }

    public function testConvert0Jpeg()
    {
        $fileName = 'tests/Support/Files/Portrait_exorientation_0.jpg';
        $tempFile = tempnam('/tmp', 'evftest');
        @copy(base_path($fileName), $tempFile);
        $this->assertTrue(file_exists($tempFile));
        $newFile = PhotoAssessService::convert($tempFile, 'image/jpeg');
        $this->assertNotEmpty($newFile);
        @unlink($tempFile);
    }

    public function testConvert0JpegUC()
    {
        $fileName = 'tests/Support/Files/Portrait_exorientation_0.jpg';
        $tempFile = tempnam('/tmp', 'evftest');
        @copy(base_path($fileName), $tempFile);
        $this->assertTrue(file_exists($tempFile));
        $newFile = PhotoAssessService::convert($tempFile, 'IMAGE/JPEG');
        $this->assertNotEmpty($newFile);
        @unlink($tempFile);
    }

    public function testConvertFailNonImage()
    {
        $fileName = 'tests/Support/bootstrapTests.php';
        $tempFile = tempnam('/tmp', 'evftest');
        @copy(base_path($fileName), $tempFile);
        $this->assertTrue(file_exists($tempFile));
        $newFile = PhotoAssessService::convert($tempFile, 'image/jpg');
        $this->assertEmpty($newFile);
        @unlink($tempFile);
    }
}
