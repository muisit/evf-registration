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

class PDFGeneratorCaseTest extends TestCase
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

    public function testCase20220216()
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
            'lastname' => 'Test',
            'firstname' => 'I.Am',
            'roles' => ['Athlete WS4, Team Armourer', 'Head of Delegation', 'Referee'],
            'dates' => ['SAT 12', 'SUN 21']
        ];
        $content = $this->createBasicTemplate();
        $content["elements"] = [
//            [
//                "type" => "photo",
//                "style" => [
//                    "left" => 291,
//                   "top" => 159,
//                    "width" => 101.11111111111111,
//                    "height" => 130,
//                    "zIndex" => 1
//                ],
//                "ratio" => 0.7777777777777778,
//                "hasRatio" => true,
//                "index" => 226878,
//                "test" => base_path('tests/Support/Files/fish.jpg')
//            ],
            [
                "type" => "name",
                "text" => "NOSUCHNAME, nosuchperson",
                "style" => [
                    "width" => 270,
                    "height" => 29,
                    "left" => 19,
                    "top" => 159,
                    "fontSize" => 17,
                    "fontStyle" => "bold",
                    "fontFamily" => "Sans",
                    "zIndex" => 6,
                    "color" => "#003b76"
                ],
                "hasFontSize" => true,
                "hasColour" => true,
                "resizeable" => true,
                "index" => 143656,
                "name" => "last",
                "color2" => "#003b76"
            ],
            [
                "type" => "name",
                "text" => "NOSUCHNAME, nosuchperson",
                "style" => [
                    "width" => 270,
                    "height" => 27,
                    "left" => 19,
                    "top" => 187,
                    "fontSize" => 14,
                    "fontStyle" => "bold",
                    "fontFamily" => "Sans",
                    "zIndex" => 6,
                    "color" => "#003b76"
                ],
                "hasFontSize" => true,
                "hasColour" => true,
                "resizeable" => true,
                "index" => 112980,
                "name" => "first",
                "color2" => "#003b76"
            ],
            [
                "type" => "country",
                "text" => "EUR",
                "style" => [
                    "width" => 270,
                    "height" => 44,
                    "left" => 18,
                    "top" => 244,
                    "fontSize" => 30,
                    "fontStyle" => "bold",
                    "fontFamily" => "Sans",
                    "zIndex" => 6,
                    "color" => "#003b76"
                ],
                "hasFontSize" => true,
                "hasColour" => true,
                "resizeable" => true,
                "index" => 793172,
                "color2" => "#003b76"
            ],
            [
                "type" => "img",
                "style" => [
                    "left" => 20,
                    "top" => 391,
                    "width" => 101.8018018018018,
                    "height" => 100,
                    "zIndex" => 3
                ],
                "hasRatio" => true,
                "ratio" => 1.018018018018018,
                "file_id" => "logo",
                "index" => 567581
            ],
            [
                "type" => "box",
                "style" => [
                    "left" => 19,
                    "top" => 305,
                    "width" => 270,
                    "height" => 60,
                    "backgroundColor" => "#003b76",
                    "zIndex" => 1
                ],
                "resizeable" => true,
                "hasBackgroundColour" => true,
                "index" => 239660,
                "backgroundColor2" => "#003b76"
            ],
            [
                "type" => "roles",
                "style" => [
                    "width" => 260,
                    "height" => 58,
                    "left" => 22,
                    "top" => 306,
                    "fontSize" => 18,
                    "fontStyle" => "bold",
                    "fontFamily" => "Sans",
                    "zIndex" => 1,
                    "color" => "#ffffff"
                ],
                "hasFontSize" => true,
                "hasColour" => true,
                "resizeable" => true,
                "index" => 34780,
                "color2" => "#ffffff"
            ],
// freshly generated qr code causes hash mismatch
//            [
//                "type" => "qr",
//                "style" => [
//                    "left" => 165,
//                    "top" => 391,
//                    "width" => 100,
//                    "height" => 100,
//                    "zIndex" => 2
//                ],
//                "resizeable" => true,
//                "hasRatio" => true,
//                "ratio" => 1,
//                "index" => 887448,
//                "link" => "https:\/\/event.com"
//           ],
            [
                "type" => "box",
                "style" => [
                    "left" => 297,
                    "top" => 389,
                    "width" => 100,
                    "height" => 100,
                    "backgroundColor" => "#003b76",
                    "zIndex" => 1
                ],
                "resizeable" => true,
                "hasBackgroundColour" => true,
                "index" => 257294,
                "backgroundColor2" => "#003b76"
            ],
            [
                "type" => "dates",
                "style" => [
                    "width" => 100,
                    "height" => 63,
                    "left" => 297,
                    "top" => 413,
                    "fontSize" => 19,
                    "fontStyle" => "bold",
                    "fontFamily" => "Sans",
                    "zIndex" => 1,
                    "color" => "#ffffff"
                ],
                "hasFontSize" => true,
                "hasColour" => true,
                "resizeable" => true,
                "index" => 609550,
                "color2" => "#ffffff"
            ],
            [
                "type" => "cntflag",
                "style" => [
                    "left" => 302,
                    "top" => 305,
                    "width" => 80,
                    "height" => 60,
                    "zIndex" => 2
                ],
                "hasRatio" => true,
                "ratio" => 1.3333333333333333,
                "index" => 282564
            ],
            [
                "type" => "text",
                "text" => "Test Event",
                "style" => [
                    "left" => 89,
                    "top" => 43,
                    "fontSize" => 40,
                    "zIndex" => 1,
                    "color" => "#000000"
                ],
                "hasFontSize" => true,
                "hasColour" => true,
                "resizeable" => true,
                "index" => 262897
            ]
        ];
        $template->content = json_encode($content);
        $accreditation->data = json_encode($accreditationData);
        $generator->generate($accreditation);
        $generator->pdf->setFileId(md5("testCase20220216"));

        $path = tempnam(null, "pdftest");
        //$path = base_path('testcase20220216.pdf');
        $generator->save($path);
        $hash = hash_file("md5", $path);
        @unlink($path);
        $this->assertEquals("fd5b77b64c65715ad8c88f091d4694e0", $hash);
    }
}
