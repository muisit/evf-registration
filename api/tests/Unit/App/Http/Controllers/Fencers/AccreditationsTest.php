<?php

namespace Tests\Unit\App\Http\Controllers\Fencers;

use App\Models\Country;
use Tests\Unit\TestCase;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\EventRole as EventRoleData;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\Registration as RegistrationData;
use Tests\Support\Data\Accreditation as AccreditationData;

class AccreditationsTest extends TestCase
{
    public function fixtures()
    {
        UserData::create();
        FencerData::create();
        RegistrarData::create();
        EventRoleData::create();
        //RegistrationData::create(); // creating registrations creates accreditations as well
        AccreditationData::create();
    }

    public function testRoute()
    {
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->get('/fencers/' . FencerData::MCAT1 . '/accreditations', ['event' => EventData::EVENT1]);
        $output = $this->response->json();
        $this->assertTrue($output !== false);
        $this->assertTrue(is_array($output));
        $this->assertCount(1, $output);
        $this->assertEquals(AccreditationData::MFCAT1, $output[0]['id']);

        // test user 4 is organisation
        $this->session(['wpuser' => UserData::TESTUSER4])
            ->get('/fencers/' . FencerData::MCAT1 . '/accreditations', ['event' => EventData::EVENT1])
            ->assertStatus(200);
    }

    public function testUnAuthorised()
    {
        $this->get('/fencers/' . FencerData::MCAT1 . '/accreditations', ['event' => EventData::EVENT1])
            ->assertStatus(401);

        // test user 5 has no privileges
        $this->session(['wpuser' => UserData::TESTUSER5])
            ->get('/fencers/' . FencerData::MCAT1 . '/accreditations', ['event' => EventData::EVENT1])
            ->assertStatus(403);

        // user id does not exist
        $this->session(['wpuser' => UserData::NOSUCHID])
            ->get('/fencers/' . FencerData::MCAT1 . '/accreditations', ['event' => EventData::EVENT1])
            ->assertStatus(403);

        // GET route only
        $this->session(['wpuser' => UserData::NOSUCHID])
            ->post('/fencers/' . FencerData::MCAT1 . '/accreditations', ['event' => EventData::EVENT1])
            ->assertStatus(400);

        // test user 4 is not organisation for NOSUCHEVENT
        $this->session(['wpuser' => UserData::TESTUSER4])
            ->get('/fencers/' . FencerData::MCAT1 . '/accreditations', ['event' => EventData::NOSUCHEVENT])
            ->assertStatus(403);

        // no such fencer
        $this->session(['wpuser' => UserData::TESTUSER4])
            ->get('/fencers/' . FencerData::NOSUCHFENCER . '/accreditations', ['event' => EventData::EVENT1])
            ->assertStatus(403);
    }
}
