<?php

namespace Tests\Unit\App\Support\Services;

use App\Models\Country;
use App\Models\Fencer;
use App\Support\Services\DuplicateFencerService;
use App\Support\Traits\EVFUser;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Unit\TestCase;

class DuplicateFencerServiceTest extends TestCase
{
    public function fixtures()
    {
        FencerData::create();
    }

    public function testCheck()
    {
        $fencerDuplicate = Fencer::where('fencer_id', FencerData::MCAT2)->first();
        $service = new DuplicateFencerService();

        // only check on these fields
        $fencer = [
            "id" => $fencerDuplicate->fencer_id,
            "lastName" => $fencerDuplicate->fencer_surname,
            "firstName" => $fencerDuplicate->fencer_firstname,
            "dateOfBirth" => $fencerDuplicate->fencer_dob
        ];

        // first check that the fencer does not return itself
        $result = $service->check($fencer);
        $this->assertEmpty($result);

        // unknown id returns the duplicate fencer
        $fencer['id'] = FencerData::NOSUCHFENCER;
        $result = $service->check($fencer);
        $this->assertNotEmpty($result);
        $this->assertEquals(FencerData::MCAT2, $result->fencer_id);

        // existing ID also returns the duplicate fencer
        $fencer['id'] = FencerData::MCAT1;
        $result = $service->check($fencer);
        $this->assertNotEmpty($result);
        $this->assertEquals(FencerData::MCAT2, $result->fencer_id);

        // change in names or date-of-birth returns empty
        $fencer['lastName'] .= 'd';
        $result = $service->check($fencer);
        $this->assertEmpty($result);
        $fencer['lastName'] = $fencerDuplicate->fencer_surname;

        $fencer['firstName'] .= 'd';
        $result = $service->check($fencer);
        $this->assertEmpty($result);
        $fencer['firstName'] = $fencerDuplicate->fencer_firstname;

        $fencer['dateOfBirth'] = '1910-02-12';
        $result = $service->check($fencer);
        $this->assertEmpty($result);
        $fencer['dateOfBirth'] = $fencerDuplicate->fencer_dob;
    }
}
