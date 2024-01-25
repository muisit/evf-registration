<?php

namespace Tests\Unit\App\Support;

use App\Models\Accreditation;
use App\Models\AccreditationTemplate;
use App\Models\Event;
use App\Models\EventRole;
use App\Models\Fencer;
use App\Models\Country;
use App\Models\Registration;
use App\Models\Role;
use App\Models\RoleType;
use App\Support\Services\PDFGenerator;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\Registration as RegistrationData;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\EventRole as EventRoleData;
use Tests\Support\Data\SideEvent as SideEventData;
use Tests\Support\Data\Accreditation as AccreditationData;
use Tests\Support\Data\AccreditationTemplate as TemplateData;
use Tests\Unit\TestCase;

// there are 17 registrations
// 1 registration for MFCAT1 (id 1)
// 1 registration for MFCAT2 (id 2)
// 1 registration for WSCAT1 (id 3)
// 4 registrations for MFTEAM (id 4)
// 3 registrations for the cocktail dinatoire (id 5, MCAT1, MCAT5, WCAT4)
// 2 registrations for the gala (id 6, WCAT3, MCAT3)
// 5 registrations for support roles (3x MCAT5, 2x MCAT4)
//
// there are 8 accreditations
// 1 for MCAT1, with 2 registrations (MFCAT1, MFTEAM1)
// 1 for MCAT1B, with 1 registration (MFTEAM2)
// 1 for MCAT1C, with 1 registration (MFTEAM3)
// 1 for MCAT2, with 2 registrations (MFCAT2, MFTEAM1)
// 1 for WCAT1, with 1 registration (WSCAT1)
// 2 for MCAT5, with 2 country support roles and 1 organisation support role
// 1 for MCAT4, with 2 support roles (organisation)

class PDFGeneratorElementTest extends TestCase
{
    public function fixtures()
    {
        TemplateData::create();
        FencerData::create();
        EventData::create();
        EventRoleData::create();
        SideEventData::create();
        RegistrationData::create();
        AccreditationData::create();
    }

    public function createBasicTemplate()
    {
        return [
            'elements' => [
            ],
            'pictures' => [
                [
                    'file_id' => 'fish1',
                    'path' => base_path('tests/Support/Files/fish.jpg'), // explicit path for test environment
                    'file_ext' => 'jpg'
                ],
                [
                    'file_id' => 'fish2',
                    'path' => base_path('tests/Support/Files/fish.png'),
                    'file_ext' => 'png'
                ],
                [
                    'file_id' => 'logo',
                    'path' => base_path('tests/Support/Files/logo.png'),
                    'file_ext' => 'png'
                ],
                [
                    'file_id' => 'logo2',
                    'path' => base_path('tests/Support/Files/logo2.png'),
                    'file_ext' => 'png'
                ],
                [
                    'file_id' => 'logo2b',
                    'path' => base_path('tests/Support/Files/logo2.gif'),
                    'file_ext' => 'gif'
                ]
            ]
        ];
    }

    public function testText()
    {
        $accreditation = Accreditation::find(AccreditationData::MFCAT1);
        $event = Event::find(EventData::EVENT1);
        $event->event_name = "EC2020 Bruxelles";
        $event->save();
        $template = $accreditation->template;
        $generator = new PDFGenerator();
        $accreditationData = (object) [
            'print' => 'a4portrait',
            'created' => '2000-01-01', // set explicit values to allow hash comparisons
            'modified' => '2000-01-01',

        ];
        $content = $this->createBasicTemplate();

        $vals = [
            [10, 10, 200, 40, 20],
            [250, 10, 150, 40, 30],
            [20, 120, 40, 200, 15],
            [80, 120, 40, 300, 20],
            [140, 120, 40, 300, 30],
            [200, 120, 40, 300, 10]
        ];
        $colour = "#888";
        for ($i = 0; $i < sizeof($vals); $i++) {
            list($x, $y, $w, $h, $fs) = $vals[$i];

            $content["elements"][] = [
                "type" => "text",
                "text" => "This is a test with a lot of lines to see if this breaks up correctly. Add some lorem ipsum! And dolor, sit, amet, with - punctuations (sometimes) and an@email.address. Yes!",
                "style" => [
                    "left" => $x,
                    "top" => $y,
                    "width" => $w,
                    "height" => $h,
                    "color" => $colour,
                    "fontSize" => $fs
                ]
            ];
        }
        $template->content = json_encode($content);
        $accreditation->data = json_encode($accreditationData);
        $generator->generate($accreditation);
        $generator->pdf->setFileId(md5("testText"));

        $path = tempnam(null, "pdftest");
        //$path = base_path('testtext.pdf');
        $generator->save($path);
        $hash = hash_file("md5", $path);
        @unlink($path);
        $this->assertEquals("bfcac244c5a04868581131ce6490a743", $hash);
    }

    public function testText2()
    {
        $accreditation = Accreditation::find(AccreditationData::MFCAT1);
        $event = Event::find(EventData::EVENT1);
        $event->event_name = "EC2020 Bruxelles";
        $event->save();
        $template = $accreditation->template;
        $generator = new PDFGenerator();
        $accreditationData = (object) [
            'print' => 'a4portrait',
            'created' => '2000-01-01', // set explicit values to allow hash comparisons
            'modified' => '2000-01-01',

        ];
        $content = $this->createBasicTemplate();

        $lineheight = 20;
        for ($i = 0; $i < 10; $i++) {
            $fontsize = 12 + $i * 4;
            $lineheight += 1.4 * $fontsize;
            $colour = "#" . dechex(255 - ($i * 10)) . dechex(128 + ($i * 2)) . dechex(128);
            $content["elements"][] = [
                "type" => "text",
                "text" => "This is a test",
                "style" => [
                    "left" => 20 + 10 * $i,
                    "top" => $lineheight,
                    "width" => 400,
                    "height" => 100,
                    "color" => $colour,
                    "fontSize" => $fontsize
                ]
            ];
        }

        $template->content = json_encode($content);
        $accreditation->data = json_encode($accreditationData);
        $generator->generate($accreditation);
        $generator->pdf->setFileId(md5("testText2"));

        $path = tempnam(null, "pdftest");
        //$path = base_path('testtext2.pdf');
        $generator->save($path);
        $hash = hash_file("md5", $path);
        @unlink($path);
        $this->assertEquals("bbdcf81b7d12974b2be40c9596897268", $hash);
    }

    public function testFonts()
    {
        $accreditation = Accreditation::find(AccreditationData::MFCAT1);
        $event = Event::find(EventData::EVENT1);
        $event->event_name = "EC2020 Bruxelles";
        $event->save();
        $template = $accreditation->template;
        $generator = new PDFGenerator($accreditation);
        $accreditationData = (object) [
            'print' => 'a4portrait',
            'created' => '2000-01-01', // set explicit values to allow hash comparisons
            'modified' => '2000-01-01',

        ];
        $content = $this->createBasicTemplate();

        $offset = 20;
        foreach (
            [
            "Courier", "Courier Bold", "Courier Italic","Courier Bold Italic",
            "Helvetica","Helvetica Bold","Helvetica Italic","Helvetica Bold Italic",
            "Times","Times Bold","Times Italic","Times Bold Italic",
            "DejaVuSans","DejaVuSans Bold","DejaVuSans Italic","DejaVuSans Bold Italic",
            ] as $fontname
        ) {
            $element = [
                "type" => "text",
                "text" => "This is a test in $fontname",
                "style" => [
                    "left" => 20,
                    "top" => $offset,
                    "width" => 400,
                    "height" => 100,
                    "color" => "#1234ab",
                    "fontSize" => "10",
                    "fontFamily" => $fontname
                ]
            ];
            $content["elements"][] = $element;
            $offset += 32;
        }

        $template->content = json_encode($content);
        $accreditation->data = json_encode($accreditationData);
        $generator->generate($accreditation);
        $generator->pdf->setFileId(md5("testFonts"));

        $path = tempnam(null, "pdftest");
        //$path = base_path('testfonts.pdf');
        $generator->save($path);
        $hash = hash_file("md5", $path);
        @unlink($path);
        $this->assertEquals("29ee2ea4ddc1419420c3cce1b1877e48", $hash);
    }

    public function testFonts2()
    {
        $accreditation = Accreditation::find(AccreditationData::MFCAT1);
        $event = Event::find(EventData::EVENT1);
        $event->event_name = "EC2020 Bruxelles";
        $event->save();
        $template = $accreditation->template;
        $generator = new PDFGenerator();
        $accreditationData = (object) [
            'print' => 'a4portrait',
            'created' => '2000-01-01', // set explicit values to allow hash comparisons
            'modified' => '2000-01-01',

        ];
        $content = $this->createBasicTemplate();

        $offset = 20;
        foreach (
            [
            "DejaVuSans Condensed","DejaVuSans Condensed Bold","DejaVuSans Condensed Italic","DejaVuSans Condensed Bold Italic",
            "DejaVuSans Mono","DejaVuSans Mono Bold","DejaVuSans Mono Italic","DejaVuSans Mono Bold Italic",
            "FreeSans","FreeSans Bold","FreeSans Italic","FreeSans Bold Italic",
            "FreeMono","FreeMono Bold","FreeMono Italic","FreeMono Bold Italic",
            ] as $fontname
        ) {
            $element = [
                "type" => "text",
                "text" => "This is a test in $fontname",
                "style" => [
                    "left" => 20,
                    "top" => $offset,
                    "width" => 400,
                    "height" => 100,
                    "color" => "#1234ab",
                    "fontSize" => "10",
                    "fontFamily" => $fontname
                ]
            ];
            $content["elements"][] = $element;
            $offset += 32;
        }

        $template->content = json_encode($content);
        $accreditation->data = json_encode($accreditationData);
        $generator->generate($accreditation);
        $generator->pdf->setFileId(md5("testFonts2"));

        $path = tempnam(null, "pdftest");
        //$path = base_path('testfonts2.pdf');
        $generator->save($path);
        $hash = hash_file("md5", $path);
        @unlink($path);
        $this->assertEquals("22cca01707a152019596d041c66298c9", $hash);
    }

    public function testFonts3()
    {
        $accreditation = Accreditation::find(AccreditationData::MFCAT1);
        $event = Event::find(EventData::EVENT1);
        $event->event_name = "EC2020 Bruxelles";
        $event->save();
        $template = $accreditation->template;
        $generator = new PDFGenerator();
        $accreditationData = (object) [
            'print' => 'a4portrait',
            'created' => '2000-01-01', // set explicit values to allow hash comparisons
            'modified' => '2000-01-01',

        ];
        $content = $this->createBasicTemplate();

        $offset = 20;
        foreach (
            [
            "FreeSerif","FreeSerif Bold","FreeSerif Italic","FreeSerif Bold Italic",
            "Eurofurence","Eurofurence Bold","Eurofurence Italic","Eurofurence Bold Italic",
            "Eurofurence Light","Eurofurence Light Italic",
            ] as $fontname
        ) {
            $element = [
                "type" => "text",
                "text" => "This is a test in $fontname",
                "style" => [
                    "left" => 20,
                    "top" => $offset,
                    "width" => 400,
                    "height" => 100,
                    "color" => "#1234ab",
                    "fontSize"=>"10",
                    "fontFamily" => $fontname
                ]
            ];
            $content["elements"][] = $element;
            $offset += 32;
        }

        $template->content = json_encode($content);
        $accreditation->data = json_encode($accreditationData);
        $generator->generate($accreditation);
        $generator->pdf->setFileId(md5("testFonts3"));

        $path = tempnam(null, "pdftest");
        //$path = base_path('testfonts3.pdf');
        $generator->save($path);
        $hash = hash_file("md5", $path);
        @unlink($path);
        $this->assertEquals("92a1dccb627bdd1061833a7ff42571e6", $hash);
    }
}
