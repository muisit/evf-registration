<?php

namespace Tests\Unit\App\Models\Schemas;

use App\Models\Schemas\Competition as Schema;
use App\Models\Competition;
use Tests\Unit\TestCase;

class CompetitionTest extends TestCase
{
    public function testCreate()
    {
        $competition = new Competition();
        $competition->competition_id = 12;
        $competition->competition_weapon = 11;
        $competition->competition_category = 38;
        $competition->competition_opens = "aaaaa";
        $competition->competition_weapon_check = "bbbb";
        $schema = new Schema($competition);

        $this->assertEquals($competition->competition_id, $schema->id);
        $this->assertEquals($competition->competition_weapon, $schema->weapon);
        $this->assertEquals($competition->competition_category, $schema->category);
        $this->assertEquals($competition->competition_opens, $schema->starts);
        $this->assertEquals($competition->competition_weapon_check, $schema->weaponsCheck);
    }
}
