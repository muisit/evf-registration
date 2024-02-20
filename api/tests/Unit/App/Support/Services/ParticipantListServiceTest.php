<?php

namespace Tests\Unit\App\Support;

use App\Models\SideEvent;
use App\Models\Competition;
use App\Models\Registration;
use App\Models\Fencer;
use App\Support\Services\ParticipantListService;
use App\Support\Services\RegistrationXMLService;
use App\Support\Services\RegistrationCSVService;
use Illuminate\Foundation\Application;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\SideEvent as EventData;
use Tests\Support\Data\Registration as RegistrationData;
use Tests\Unit\TestCase;

class ParticipantListServiceTest extends TestCase
{
    public function testGenerateCSV()
    {
        $sideEvent = SideEvent::find(EventData::MFCAT1);
        $reg = Registration::find(RegistrationData::REG2);
        $reg->registration_event = EventData::MFCAT1;
        $reg->save();
        $reg = Registration::find(RegistrationData::REG3);
        $reg->registration_event = EventData::MFCAT1;
        $reg->save();

        $this->initMock(function ($v, $type) {
            if ($type == 'event') {
                $this->assertNotEmpty($v);
                $this->assertEquals(EventData::MFCAT1, $v->getKey());
                return true;
            }
            else {
                $this->assertCount(3, $v);
                $this->assertInstanceOf(Registration::class, $v[0]);
                // this tests the sorting based on name. WCAT1 and MCAT1 have the same surname
                $this->assertEquals(FencerData::WCAT1, $v[0]->registration_fencer);
                $this->assertEquals(FencerData::MCAT1, $v[1]->registration_fencer);
                $this->assertEquals(FencerData::MCAT2, $v[2]->registration_fencer);
                return true;
            }

        }, function ($v, $type) {
            if ($type == 'registrations') {
                $this->assertCount(3, $v);
                $this->assertInstanceOf(Registration::class, $v[0]);
                $this->assertEquals(FencerData::WCAT1, $v[0]->registration_fencer);
                $this->assertEquals(FencerData::MCAT1, $v[1]->registration_fencer);
                $this->assertEquals(FencerData::MCAT2, $v[2]->registration_fencer);
                return true;
            }
            else if ($type == 'headers') {
                $this->assertCount(10, $v);
                return true;
            }
            else {
                $this->assertNotEmpty($v);
                $this->assertTrue(isset($v->isCompetition));
                $this->assertTrue($v->isCompetition);
                return true;
            }
        });
        $service = new ParticipantListService($sideEvent);
        ob_start();
        $service->asCSV("test.csv");
        $service->asXML("test.xml");
        ob_end_clean();
    }



    private function initMock($checkXML, $checkCSV)
    {
        $sut = $this;
        $this->app->bind(RegistrationXMLService::class, function (Application $app) use ($sut, $checkXML) {
            $dom = new \DOMDocument();
            $root = $dom->createElement('BaseCompetitionIndividuelle');
            $implementation = new \DOMImplementation();
            $doctype = $implementation->createDocumentType('BaseCompetitionIndividuelle');
            $tireurs = $dom->createElement("Tireurs");
            $tireurs->appendChild($dom->createElement("Tireur"));
            $tireurs->appendChild($dom->createElement("Tireur"));
            $tireurs->appendChild($dom->createElement("Tireur"));
            $tireurs->appendChild($dom->createElement("Tireur"));
            $root->appendChild($tireurs);
            $dom->appendChild($doctype);
            $dom->appendChild($root);

            $generator = $sut->createMock(RegistrationXMLService::class);
            $generator->expects($sut->once())->method('generate')->with(
                $sut->callback(fn ($v) => $checkXML($v, 'event')),
                $sut->callback(fn ($v) => $checkXML($v, 'registrations'))
            )->will($sut->returnValue($dom->saveXML()));
            return $generator;
        });
        $this->app->bind(RegistrationCSVService::class, function (Application $app) use ($sut, $checkCSV) {
            $generator = $sut->createMock(RegistrationCSVService::class);
            $generator->expects($sut->once())->method('generate')->with(
                $sut->callback(fn ($v) => $checkCSV($v, 'registrations')),
                $sut->callback(fn ($v) => $checkCSV($v, 'headers')),
                $sut->callback(fn ($v) => $checkCSV($v, 'config'))
            )->will($sut->returnValue([[1,2,3,4,5,6,7,8], [1,2,3,4,5,6,7,8]]));
            return $generator;
        });
    }
}
