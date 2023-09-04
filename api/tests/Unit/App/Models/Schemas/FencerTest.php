<?php

namespace Tests\Unit\App\Models\Schemas;

use App\Models\Schemas\Fencer as Schema;
use App\Models\Fencer;
use Tests\Unit\TestCase;

class FencerTest extends TestCase
{
    public function testCreate()
    {
        $data = new Fencer();
        $data->fencer_id = 12;
        $data->fener_firstname = 'whola';
        $data->fencer_surname = 'bola';
        $data->fencer_country = 1022;
        $data->fencer_gender = 'bolblas';
        $data->fencer_dob = 'blabla';
        $data->fencer_picture = 'bloblo';

        $schema = new Schema($data);

        $this->assertEquals($data->fencer_id, $schema->id);
        $this->assertEquals($data->fencer_firstname, $schema->firstName);
        $this->assertEquals($data->fencer_surname, $schema->lastName);
        $this->assertEquals($data->fencer_gender, $schema->gender);
        $this->assertEquals($data->fencer_country, $schema->countryId);
        $this->assertEquals($data->fencer_dob, $schema->dateOfBirth);
        $this->assertEquals($data->fencer_picture, $schema->photoStatus);

        $data->fencer_country = -1;
        $data->fencer_dob = null;
        $data->fencer_picture = null;
        $schema = new Schema($data);
        $this->assertEmpty($schema->countryId);
        $this->assertEmpty($schema->dateOfBirth);
        $this->assertEmpty($schema->photoStatus);
    }
}
