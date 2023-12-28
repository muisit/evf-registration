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

class PDFGeneratorImageTest extends TestCase
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

    public function testPhotoId()
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
        $vals = [
            [10, 10, 100],
            [20, 120, 50],
            [150, 10, 150],
            [150, 200, 120]
        ];
        for ($i = 0; $i < sizeof($vals); $i++) {
            list($x, $y, $w) = $vals[$i];
            $content["elements"][] = [
                "type" => "photo",
                "test" => base_path('tests/Support/Files/fish.jpg'),
                "style" => [
                    "left" => $x,
                    "top" => $y,
                    "width" => $w,
                    "height" => 100000
                ]
            ];
        }
        $template->content = json_encode($content);
        $template->save();
        $accreditation->data = json_encode($accreditationData);
        $generator->generate($accreditation);
        $generator->pdf->setFileId(md5("testbasicelements"));

        $path = tempnam(null, "pdftest");
        //$path = base_path('testpdfphoto.pdf');
        $generator->save($path);
        $hash = hash_file("md5", $path);
        @unlink($path);
        $this->assertEquals("4c34414c0bf3b9d1526ad4cf8938027d", $hash);
    }

    public function testImages()
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
        $content["elements"][] = [
            "type" => "img",
            "file_id" => "logo",
            "hasRatio" => true,
            "ratio" => 1.018018018018018,
            "style" => ["left" => 20, "top" => 391, "width" => 101.8018018018018, "height" => 100, "zIndex" => 2]
        ];

        $template->content = json_encode($content);
        $template->save();
        $accreditation->data = json_encode($accreditationData);
        $generator->generate($accreditation);
        $generator->pdf->setFileId(md5("testimages"));

        $path = tempnam(null, "pdftest");
        //$path = base_path('testimages.pdf');
        $generator->save($path);
        $hash = hash_file("md5", $path);
        @unlink($path);
        $this->assertEquals("9fa5f2a4faa7bfb7ddc113b30368f6cb", $hash);
    }

    public function testImages2()
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
        $vals=[
            [10, 10, 100],
            [20, 120, 50],
            [150, 10, 150],
            [150, 200, 120]
        ];

        for ($i = 0; $i < sizeof($vals); $i++) {
            list($x, $y, $w) = $vals[$i];
            $content["elements"][] = [
                "type" => "img",
                "file_id" => "fish2",
                "ratio" => 1.5081967213114753,
                "style" => [
                    "left" => $x,
                    "top" => $y,
                    "width" => $w,
                    "height" => 100000
                ]
            ];
        }

        $template->content = json_encode($content);
        $template->save();
        $accreditation->data = json_encode($accreditationData);
        $generator->generate($accreditation);
        $generator->pdf->setFileId(md5("testimages2"));

        $path = tempnam(null, "pdftest");
        //$path = base_path('testimages2.pdf');
        $generator->save($path);
        $hash = hash_file("md5", $path);
        @unlink($path);
        $this->assertEquals("30ee00eb7ff6070f7578eff1b8b78ff0", $hash);
    }

    public function testImages3()
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
        $vals=[
            [10, 10, 100],
            [20, 120, 50],
            [150, 10, 150],
            [150, 200, 120]
        ];

        for ($i = 0; $i < sizeof($vals); $i++) {
            list($x, $y, $w) = $vals[$i];
            $content["elements"][] = [
                "type" => "img",
                "file_id" => "fish1",
                "ratio" => 1.5081967213114753,
                "style" => [
                    "left" => $x,
                    "top" => $y,
                    "width" => $w,
                    "height" => 100000
                ]
            ];
        }

        $template->content = json_encode($content);
        $template->save();
        $accreditation->data = json_encode($accreditationData);
        $generator->generate($accreditation);
        $generator->pdf->setFileId(md5("testimages3"));

        $path = tempnam(null, "pdftest");
        //$path = base_path('testimages3.pdf');
        $generator->save($path);
        $hash = hash_file("md5", $path);
        @unlink($path);
        $this->assertEquals("53ad05f0f4b1db30332d84f14cd22490", $hash);
    }
}
