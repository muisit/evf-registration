<?php
namespace App\Support\Services;

class PhotoAssessService
{
    public static function convert($filename, $type)
    {
        $type = strtolower($type);
        \Log::debug("converting file $filename of type $type");
        $image = null;
        switch ($type) {
            case 'image/jpg':
            case 'image/jpeg':
                $image = self::rotateImage(imagecreatefromjpeg($filename), $filename);
                break;
            case 'image/png':
                $image = imagecreatefrompng($filename);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($filename);
                break;
            default:
                \Log::error("Unable to transform uploaded image $filename/$type");
                return null;
        }

        return self::doConvert($image, $filename);
    }

    private static function doConvert($image, $filename)
    {
        $image = self::convertToTrueColor($image);
        $image = self::scaleAndCrop($image);

        if (!empty($image)) {
            //\Log::debug("converting image to JPEG, 90% quality");
            // convert to JPEG
            imagejpeg($image, $filename, 90);
            imagedestroy($image);
            return $filename;
        }
        return null;
    }

    private static function rotateImage($image, $filename)
    {
        $exif = \exif_read_data($filename);
            
        if (!empty($exif['Orientation'])) {
            switch ($exif['Orientation']) {
                case 2: //flipped
                case 1: // default orientation
                case 0: // no orientation
                    break;
                case 4: // flipped
                case 3:
                    $image = imagerotate($image, 180, 0);
                    break;
                case 7: // flipped
                case 8:
                    $image = imagerotate($image, 90, 0);
                    break;
                case 5: // flipped
                case 6:
                    $image = imagerotate($image, -90, 0);
                    break;
            }
            switch ($exif['Orientation']) {
                default:
                    // no mirroring
                    break;
                case 2:
                case 4:
                case 5:
                case 7:
                    imageflip($image, IMG_FLIP_HORIZONTAL);
                    break;
            }
        }
        return $image;
    }


    private static function scaleAndCrop($image)
    {
        if (!empty($image)) {
            $wh = self::determineCropDimensions($image);

            if ($wh !== null) {
                $wh = self::scaleDimensions($wh);
                $image = self::scaleAndCropImage($image, $wh);
            }
            else {
                // incorrect dimensions detected, this is not a correct image
                imagedestroy($image);
                $image = null;
            }
        }
        return $image;
    }

    private static function scaleAndCropImage($image, $wh)
    {
        if (!empty($image)) {
            $offX = $wh[0];
            $offY = $wh[1];
            $w = ceil($wh[2]);
            $h = ceil($wh[3]);
            $destX = $wh[4];
            $destY = $wh[5];
            $image2 = imagecreatetruecolor($destX, $destY);
            \Log::debug("resampling by first cropping from " . imagesx($image) . " by " . imagesy($image) . " to image of size $w by $h from offset $offX, $offY, then scaling from $w by $h to $destX by $destY");
            // source, destination, destX, destY, srcX, srcY, dstWidth, destHeight, srcWidth, srcHeight
            if (imagecopyresampled($image2, $image, 0, 0, $offX, $offY, $destX, $destY, $w, $h)) {
                $image = $image2;
            }
            else {
                $image = null;
                \Log::error("copy-resampled fails");
            }
        }
        return $image;
    }

    private static function scaleDimensions($wh)
    {
        // the ratio should match more or less, but due to pixel count being integer, it can
        // be smaller or larger than the ideal ratio (unless width and height are exactly correct)
        $destX = 413;
        $destY = 531;
        $ratio = $destX / $destY;
        $imageRatio = $wh[2] / $wh[3];
        if ($ratio > $imageRatio) {
            //\Log::debug("adjusting scaled height based on ratio");
            $destY = intval($destX / $ratio);
        }
        else {
            //\Log::debug("adjusting scaled width based on ratio");
            $destX = intval($destY * $ratio);
        }
        return [$wh[0], $wh[1], $wh[2], $wh[3], $destX, $destY];
    }

    private static function determineCropDimensions($image)
    {
        $ratio = 413.0 / 531.0;
        $w = imagesx($image);
        $h = imagesy($image);
        if ($h <= 0 || $w <= 0) {
            \Log::error("images has incorrect dimensions " . json_encode([$ratio, $w, $h]));
            return null;
        }

        $imageRatio = $w / $h;
        if ($ratio > $imageRatio) {
            // image is too high
            $requiredHeight = intval($w / $ratio);
            $offsetY = ($h - $requiredHeight) / 2;
            //\Log::debug("image is too high, cropping the height from $h to $requiredHeight");
            return [0, $offsetY, $w, $requiredHeight];
        }
        else if ($ratio < $imageRatio) {
            // image is too wide
            $requiredWidth = intval($h * $ratio);
            $offsetX = ($w - $requiredWidth) / 2;
            //\Log::debug("image is too wide, cropping the width from $w to $requiredWidth");
            return [$offsetX, 0, $requiredWidth, $h];
        }
        // else ratio is just fine
        return [0, 0, $w, $h];
    }

    private static function convertToTrueColor($image)
    {
        if ($image && !imageistruecolor($image)) {
            //\Log::debug("converting image to true color");
            $bg = imagecreatetruecolor(imagesx($image), imagesy($image));
            imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
            imagealphablending($bg, true);
            imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
            imagedestroy($image);
            return $bg;
        }
        return $image;
    }
}