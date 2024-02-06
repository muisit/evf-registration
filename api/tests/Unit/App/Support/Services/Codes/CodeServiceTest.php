<?php

namespace Tests\Unit\App\Support\Codes;

use App\Models\AccreditationUser;
use App\Models\Event;
use App\Models\Schemas\Code;
use Tests\Support\Data\AccreditationUser as UserData;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\Fencer as FencerData;
use App\Support\Services\Codes\CodeService;
use Tests\Unit\TestCase;
use Illuminate\Support\Facades\Auth;

class CodeServiceTest extends TestCase
{
    public function testCreate()
    {
        $service = new CodeService();
        $this->assertEmpty($service->event);
        $this->assertNotEmpty($service->result);
        $this->assertEquals("fail", $service->result->action);
        $this->assertEquals("error", $service->result->status);
        $this->assertEquals(0, $service->result->eventId);
        $this->assertEmpty($service->result->message);
        $this->assertCount(0, $service->errors);

        $service = new CodeService(Event::find(EventData::EVENT1));
        $this->assertNotEmpty($service->event);
        $this->assertEquals(EventData::EVENT1, $service->event->getKey());
        $this->assertEquals(EventData::EVENT1, $service->result->eventId);
    }

    public function testSetEvent()
    {
        $service = new CodeService();
        $service->setEvent(Event::find(EventData::EVENT1));
        $this->assertNotEmpty($service->event);
        $this->assertEquals(EventData::EVENT1, $service->event->getKey());
        $this->assertEquals(EventData::EVENT1, $service->result->eventId);

        $service->setEvent(null);
        $this->assertEmpty($service->event);
        $this->assertEquals(0, $service->result->eventId);
    }

    public function testAddError()
    {
        $service = new CodeService();
        $service->result->status = "ok";
        $this->assertEquals("ok", $service->result->status);
        $this->assertCount(0, $service->errors);

        $service->addError("Error 1");
        $this->assertCount(1, $service->errors);
        $this->assertEquals("error", $service->result->status);
        $this->assertContains("Error 1", $service->errors);
    }

    public function testHandleLoginAdmin()
    {
        $this->session([]);
        $service = new CodeService();
        $code = new Code();
        $code->original = '99058223000001';
        $code->baseFunction = 9;
        $code->addFunction = 0;
        $code->id1 = 582;
        $code->id2 = 230;
        $code->validation = 0;
        $code->payload = '0001';

        $service->handle('login', [$code]);
        $this->assertCount(0, $service->errors);
        $this->assertFalse(Auth::guest());
        $this->assertNotEmpty(Auth::user());
        $this->assertEquals(UserData::ADMIN, Auth::user()->getKey());
        $this->assertEquals('login', $service->result->action);
        $this->assertEquals('ok', $service->result->status);
        $this->assertNotEmpty($service->event);
        $this->assertEquals(EventData::EVENT1, $service->event->getKey());
        $this->assertEquals(EventData::EVENT1, $service->result->eventId);
        $this->assertEmpty($service->result->fencer);
    }

    public function testHandleLoginBadge()
    {
        $this->session([]);
        $service = new CodeService();
        $code = new Code();
        $code->original = '11127057800000';
        $code->baseFunction = 1;
        $code->addFunction = 1;
        $code->id1 = 270;
        $code->id2 = 578;
        $code->validation = 0;
        $code->payload = '0000';

        $service->handle('login', [$code]);
        $this->assertCount(0, $service->errors);
        $this->assertFalse(Auth::guest());
        $this->assertNotEmpty(Auth::user());
        $this->assertEquals(UserData::MFCAT1, Auth::user()->getKey());
        $this->assertEquals('login', $service->result->action);
        $this->assertEquals('ok', $service->result->status);
        $this->assertNotEmpty($service->event);
        $this->assertEquals(EventData::EVENT1, $service->event->getKey());
        $this->assertEquals(EventData::EVENT1, $service->result->eventId);
        $this->assertEmpty($service->result->fencer);
    }

    public function testHandleBadge()
    {
        // need some privileges for this
        $this->session(['accreditationuser' => UserData::ADMIN]);
        request()->merge(['eventObject' => Event::find(EventData::EVENT1)]);

        $service = new CodeService();
        $code = new Code();
        $code->original = '11127057800000';
        $code->baseFunction = 1;
        $code->addFunction = 1;
        $code->id1 = 270;
        $code->id2 = 578;
        $code->validation = 0;
        $code->payload = '0000';

        $service->handle('badge', [$code]);
        $this->assertCount(0, $service->errors);
        $this->assertEquals('badge', $service->result->action);
        $this->assertEquals('ok', $service->result->status);
        $this->assertNotEmpty($service->event);
        $this->assertEquals(EventData::EVENT1, $service->event->getKey());
        $this->assertEquals(EventData::EVENT1, $service->result->eventId);
        $this->assertNotEmpty($service->result->fencer);
        $this->assertEquals(FencerData::MCAT1, $service->result->fencer->id);
    }
}
