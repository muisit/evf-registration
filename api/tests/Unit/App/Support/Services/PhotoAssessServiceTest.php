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

    public function calculateHashOfFile($fname, $index)
    {
        $outfile = "tests/Support/Files/Portrait_exorientation_{$index}_out.jpg";
        $reference = imagecreatefromjpeg($outfile);
        $image = imagecreatefromjpeg($fname);
        $w = imagesx($image);
        $h = imagesy($image);
        $wr = imagesx($reference);
        $hr = imagesy($reference);

        if ($w !== $wr || $h !== $hr) {
            return "width height problem: $w vs $wr, $h vs $hr";
        }

        $data = "";
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $color = imagecolorat($image, $x, $y);
                $r = (($color & 0xFF0000) >> 16);
                $g = (($color & 0x00FF00) >> 8);
                $b = (($color & 0x0000FF) >> 0);

                $colorref = imagecolorat($image, $x, $y);
                $rr = (($colorref & 0xFF0000) >> 16);
                $gr = (($colorref & 0x00FF00) >> 8);
                $br = (($colorref & 0x0000FF) >> 0);

                $ar = abs($r - $rr) >> 1;
                $ag = abs($g - $gr) >> 1;
                $ab = abs($b - $br) >> 1;

                $data .= sprintf("%02x%02x%02x", $ar, $ag, $ab);
            }
        }
        return hash('md5', $data);
    }

    public function testConvert0()
    {
        $newFile = $this->convertFile('tests/Support/Files/Portrait_exorientation_0.jpg');
        $this->assertTrue(file_exists($newFile));
        $this->assertEquals('bd4c1a8e3277822ddd0c6a1545da3031', $this->calculateHashOfFile($newFile, 0));
        @unlink($newFile);
    }

    public function testConvert1()
    {
        $newFile = $this->convertFile('tests/Support/Files/Portrait_exorientation_1.jpg');
        $this->assertTrue(file_exists($newFile));
        $this->assertEquals('bd4c1a8e3277822ddd0c6a1545da3031', $this->calculateHashOfFile($newFile, 1));
        @unlink($newFile);
    }

    public function testConvert2()
    {
        $newFile = $this->convertFile('tests/Support/Files/Portrait_exorientation_2.jpg');
        $this->assertTrue(file_exists($newFile));
        $this->assertEquals('bd4c1a8e3277822ddd0c6a1545da3031', $this->calculateHashOfFile($newFile, 2));
        @unlink($newFile);
    }

    public function testConvert3()
    {
        $newFile = $this->convertFile('tests/Support/Files/Portrait_exorientation_3.jpg');
        $this->assertTrue(file_exists($newFile));
        $this->assertEquals('bd4c1a8e3277822ddd0c6a1545da3031', $this->calculateHashOfFile($newFile, 3));
        @unlink($newFile);
    }

    public function testConvert4()
    {
        $newFile = $this->convertFile('tests/Support/Files/Portrait_exorientation_4.jpg');
        $this->assertTrue(file_exists($newFile));
        $this->assertEquals('bd4c1a8e3277822ddd0c6a1545da3031', $this->calculateHashOfFile($newFile, 4));
        @unlink($newFile);
    }

    public function testConvert5()
    {
        $newFile = $this->convertFile('tests/Support/Files/Portrait_exorientation_5.jpg');
        $this->assertTrue(file_exists($newFile));
        $this->assertEquals('bd4c1a8e3277822ddd0c6a1545da3031', $this->calculateHashOfFile($newFile, 5));
        @unlink($newFile);
    }

    public function testConvert6()
    {
        $newFile = $this->convertFile('tests/Support/Files/Portrait_exorientation_6.jpg');
        $this->assertTrue(file_exists($newFile));
        $this->assertEquals('bd4c1a8e3277822ddd0c6a1545da3031', $this->calculateHashOfFile($newFile, 6));
        @unlink($newFile);
    }

    public function testConvert7()
    {
        $newFile = $this->convertFile('tests/Support/Files/Portrait_exorientation_7.jpg');
        $this->assertTrue(file_exists($newFile));
        $this->assertEquals('bd4c1a8e3277822ddd0c6a1545da3031', $this->calculateHashOfFile($newFile, 7));
        @unlink($newFile);
    }

    public function testConvert8()
    {
        $newFile = $this->convertFile('tests/Support/Files/Portrait_exorientation_8.jpg');
        $this->assertTrue(file_exists($newFile));
        $this->assertEquals('bd4c1a8e3277822ddd0c6a1545da3031', $this->calculateHashOfFile($newFile, 8));
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
