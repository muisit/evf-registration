<?php

namespace Tests\Unit\App\Models\Schemas;

use App\Models\Schemas\Registration as Schema;
use App\Models\Registration;
use Tests\Unit\TestCase;

class RegistrationTest extends TestCase
{
    public function testCreate()
    {
        $data = new Registration();
        $data->registration_id = 12;
        $data->registration_event = 36;
        $data->registration_mainevent = 33;
        $data->registration_fencer = 1022;
        $data->registration_role = 9928;
        $data->registration_date = 'blabla';
        $data->registration_paid = 'bloblo';
        $data->registration_paid_hod = 'blibli';
        $data->registration_payment = 'blublu';
        $data->registration_state = 'bdadasdsdad';
        $data->registration_team = 'asdadaddaddd';

        $schema = new Schema($data);

        $this->assertEquals($data->registration_id, $schema->id);
        $this->assertEquals($data->registration_event, $schema->sideEventId);
        $this->assertEquals($data->registration_fencer, $schema->fencerId);
        $this->assertEquals($data->registration_role, $schema->roleId);
        $this->assertEquals($data->registration_date, $schema->dateTime);
        $this->assertEquals($data->registration_paid, $schema->paid);
        $this->assertEquals($data->registration_paid_hod, $schema->paidHod);
        $this->assertEquals($data->registration_payment, $schema->payment);
        $this->assertEquals($data->registration_state, $schema->state);
        $this->assertEquals($data->registration_team, $schema->team);

        $data->registration_role = -1;
        $schema = new Schema($data);
        $this->assertEmpty($schema->roleId);
    }
}
