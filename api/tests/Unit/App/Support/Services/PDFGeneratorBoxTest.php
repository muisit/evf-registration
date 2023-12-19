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

class PDFGeneratorBoxTest extends TestCase
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

    public function testBox()
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
        $content["elements"] = [
            [
                "type" => "box",
                "style" => [
                    "left" => 20,
                    "top" => 20,
                    "width" => 100,
                    "height" => 100,
                    "backgroundColor" => "#1234ab"
                ]
            ],
            [
                "type" => "box",
                "style" => [
                    "left" => 200,
                    "top" => 20,
                    "width" => 220,
                    "height" => 100,
                    "backgroundColor" => "#aaffff"
                ]
            ],
            [
                "type" => "box",
                "style" => [
                    "left" => 0,
                    "top" => 394,
                    "width" => 100,
                    "height" => 200,
                    "backgroundColor" => "#ffff88"
                ]
            ],
            [
                "type" => "box",
                "style" => [
                    "left" => 210,
                    "top" => 490,
                    "width" => 200,
                    "height" => 100,
                    "backgroundColor" => "#f8f"
                ]
            ]
        ];
        $template->content = json_encode($content);
        $generator->generate($accreditationData);
        $generator->pdf->setFileId(md5("testBox"));

        $path = tempnam(null, "pdftest");
        //$path = base_path('testbox.pdf');
        $generator->saveFile($path);
        $hash = hash_file("md5", $path);
        @unlink($path);
        $this->assertEquals("4990f3ab6a52368a2537527e75eac012", $hash);
    }

    public function testBox2()
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

        for ($w = 5; $w < 420; $w += 10) {
            for ($h = 5; $h < 594; $h += 10) {
                $content["elements"][]=[
                        "type" => "box",
                        "style" => [
                            "left" => $w,
                            "top" => $h,
                            "width" => 9,
                            "height" => 9,
                            "backgroundColor" => "#" . dechex(32 + ((256 - 32) * $w / 420)) . dechex(32 + ((256 - 32) * $h / 594)) . dechex(32 + (256 - 32) * ($w + $h) / (420 + 594))
                        ]
                    ];
            }
        }
        $template->content = json_encode($content);
        $generator->generate($accreditationData);
        $generator->pdf->setFileId(md5("testBox"));

        $path = tempnam(null, "pdftest");
        //$path = base_path('testbox2.pdf');
        $generator->saveFile($path);
        $hash = hash_file("md5", $path);
        @unlink($path);
        $this->assertEquals("a34bf3df5fdcc9e4d5091a9bc21e182d", $hash);
    }
}
