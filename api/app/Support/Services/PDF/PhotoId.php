<?php

namespace App\Support\Services\PDF;

use App\Support\Services\PDFGenerator;

class PhotoId extends BasicImage
{
    public function generate($el, $content, $data)
    {
        $offset = $this->getOffset($element);
        $size = $this->getSize($element);
        $evflogger->log("adding photo at " . json_encode($offset) . "/" . json_encode($size));

        if(isset($element["test"])) {
            $evflogger->log("test set, using static path");
            $path = $element["test"];
        }
        else {
            if($this->fencer->fencer_picture != 'N') {
                $path = $this->fencer->getPath();
            }
            else {
                $path="doesnotexist"; // no photo to print
            }
        }

        // make sure the aspect ratio is retained
        $rwidth = $size[1] * 0.77777777777777779;
        $rheight = $size[0] / 0.77777777777777779;
        if($rwidth < $size[0]) {
            $size[0]=$rwidth;
        }
        else {
            $size[1] = $rheight;
        }
        $evflogger->log("testing path $path");
        if (file_exists($path)) {
            $path = $this->createWatermark($path);
            if(!empty($path) && file_exists($path)) {
                $this->putImageAt($path, array("offset"=>$offset,"size"=>$size));
                @unlink($path);
            }
        }

    }
}
