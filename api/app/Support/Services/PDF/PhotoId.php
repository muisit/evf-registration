<?php

namespace App\Support\Services\PDF;

use App\Support\Services\PDFGenerator;

class PhotoId extends BasicImage
{
    public function generate($el)
    {
        $this->parse($el);

        if (isset($el->test)) {
            $path = $el->test;
        }
        else {
            if ($this->generator->accreditation->fencer->fencer_picture != 'N') {
                $path = $this->generator->accreditation->fencer->path();
            }
            else {
                $path = "doesnotexist"; // no photo to print
            }
        }

        // make sure the aspect ratio is retained
        $goldenratio = 413.0 / 531.0;
        $rwidth = $this->size[1] * $goldenratio;
        $rheight = $this->size[0] / $goldenratio;
        if ($rwidth < $this->size[0]) {
            $this->size[0] = $rwidth;
        }
        else {
            $this->size[1] = $rheight;
        }

        if (file_exists($path)) {
            $path = $this->createWatermark($path);
            if (!empty($path) && file_exists($path)) {
                $this->insertImage($path);
                @unlink($path);
            }
        }
    }

    private function createWatermark($path)
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($ext == "jpg" || $ext == "jpeg") {
            $img = imagecreatefromjpeg($path);
        }
        else if ($ext == "png") {
            $img = imagecreatefrompng($path);
        }
        else {
            return null;
        }
        $w = imagesx($img);
        $h = imagesy($img);

        // if the ratio is not okay, we need to crop the image a bit
        // This should not happen (anymore), as we crop and scale the image at upload
        // But there are some older images available...
        // The ideal ratio is 0.7777777777777777... (width is 77% of height)
        $goldenratio = 413.0 / 531.0;
        $ratio = floatval($w) / $h;
        if ($ratio > 0.78) {
            // image is too wide, crop it sideways
            $newwidth = round($h * $goldenratio);
            $offx = round((floatval($w) - $newwidth) / 2);
            $img2 = imagecrop($img, ['x' => $offx, 'y' => 0, 'width' => $newwidth, 'height' => $h]);
            imagedestroy($img);
            $img = $img2;
        }
        else if ($ratio < 0.77) {
            // image is too high, crop in the height
            $newheight = round($w / $goldenratio);
            $offy = round((floatval($h) - $newheight) / 2);
            $img2 = imagecrop($img, ['x' => 0, 'y' => $offy, 'width' => $w, 'height' => $newheight]);
            imagedestroy($img);
            $img = $img2;
        }

        $fname = tempnam(null, "phid");
        if (file_exists($fname)) {
            $text_color = imagecolorallocate($img, 196, 196, 196);
            $ffile = $this->getFontFile("arial");
            $fsize = 19; // we start with a font size decrement
            $rotation = 0;
            $wdiff = $w + 1;
            $hdiff = $h + 1;
            while ($wdiff > $w || $hdiff > $h) {
                $fsize -= 1;
                \Log::debug("font file is $ffile");
                $box = imagettfbbox($fsize, $rotation, $ffile, $this->generator->accreditation->event->event_name);
                $maxx = max(array($box[0], $box[2], $box[4], $box[6]));
                $minx = min(array($box[0], $box[2], $box[4], $box[6]));
                $maxy = max(array($box[1], $box[3], $box[5], $box[7]));
                $miny = min(array($box[1], $box[3], $box[5], $box[7]));
                $wdiff = $maxx - $minx;
                $hdiff = $maxy - $miny;
            }
            $x = ($w - ($maxx - $minx)) / 2.0;
            $y = $h - ($maxy - $miny) - 2;
            imagettftext($img, $fsize, $rotation, $x, $y, $text_color, $ffile, $this->generator->accreditation->event->event_name);
            imagejpeg($img, $fname, 90);
            imagedestroy($img);

            // determine an output name, which needs to end with the JPG extension to
            // allow the putImageAt method to read it (which will not accept files
            // without extensions for security)
            // It would be silly if this file would exist, but better safe than sorry
            $outputname = $fname . ".jpg";
            $outputindex = 1;
            while (file_exists($outputname)) {
                $outputname = $fname . "_" . $outputindex . ".jpg";
                $outputindex += 1;
            }
            rename($fname, $outputname);
            return $outputname;
        }
        return null;
    }
}
