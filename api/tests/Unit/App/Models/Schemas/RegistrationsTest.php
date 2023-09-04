<?php

namespace Tests\Unit\App\Models\Schemas;

use App\Models\Schemas\Registrations as Schema;
use App\Models\Registration;
use App\Models\Fencer;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\Registration as RegistrationData;
use Tests\Unit\TestCase;

class RegistrationsTest extends TestCase
{
    public function fixtures()
    {
        RegistrationData::create();
        FencerData::create();
    }

    public function testCreate()
    {
        $schema = new Schema();
        $this->assertEmpty($schema->registrations);
        $this->assertEmpty($schema->fencers);

        $data = Registration::where('registration_id', RegistrationData::REG1)->first();
        $schema->add($data);
        $schema->finalize();
        $this->assertCount(1, $schema->registrations);
        $this->assertEquals(RegistrationData::REG1, $schema->registrations[0]->id);
        $this->assertCount(1, $schema->fencers);
        $this->assertEquals(FencerData::MCAT1, $schema->fencers[0]->id);
    }

    public function testDoubleEntry()
    {
        $schema = new Schema();

        $data = Registration::where('registration_id', RegistrationData::REG1)->first();
        $schema->add($data);
        $data = Registration::where('registration_id', RegistrationData::TEAM1)->first();
        $schema->add($data);
        $schema->finalize();

        $this->assertCount(2, $schema->registrations);
        $this->assertCount(1, $schema->fencers);
        $this->assertEquals(FencerData::MCAT1, $schema->fencers[0]->id);
    }

    public function testThreeFencers()
    {
        $schema = new Schema();

        $data = Registration::where('registration_id', RegistrationData::REG1)->first();
        $schema->add($data);
        $data = Registration::where('registration_id', RegistrationData::REG2)->first();
        $schema->add($data);
        $data = Registration::where('registration_id', RegistrationData::REG3)->first();
        $schema->add($data);
        $schema->finalize();

        $this->assertCount(3, $schema->registrations);
        $this->assertCount(3, $schema->fencers);
    }
}
