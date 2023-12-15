<?php

namespace Tests\Unit;

require_once('../../vendor/autoload.php');

use App\Models\AccreditationTemplate;
use App\Models\Fencer;
use App\Models\Event;
use App\Models\EventRole;
use App\Models\Competition;
use App\Models\SideEvent;
use App\Models\Registration;
use App\Models\Accreditation;
use App\Models\Registrar;
use App\Models\WPUser;
use Tests\Support\Data\Accreditation as AccreditationData;
use Tests\Support\Data\AccreditationTemplate as TemplateData;
use Tests\Support\Data\Competition as CompetitionData;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\EventRole as EventRoleData;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Support\Data\Registration as RegistrationData;
use Tests\Support\Data\SideEvent as SideEventData;
use Tests\Support\Data\WPUser as UserData;
use Illuminate\Support\Facades\DB;

class SetupTestCase extends TestCase
{
    public function clearAll()
    {
        DB::table(WPUser::tableName())->delete();
        DB::table(Registrar::tableName())->delete();
        DB::table(Accreditation::tableName())->delete();
        DB::table(Registration::tableName())->delete();
        DB::table(SideEvent::tableName())->delete();
        DB::table(Competition::tableName())->delete();
        DB::table(EventRole::tableName())->delete();
        DB::table(AccreditationTemplate::tableName())->delete();
        DB::table(Event::tableName())->delete();
        DB::table(Fencer::tableName())->delete();
    }

    public function fixtures()
    {
        $this->clearAll();
        FencerData::create();
        EventData::create();
        TemplateData::create();
        EventRoleData::create();
        CompetitionData::create();
        SideEventData::create();
        RegistrationData::create();
        AccreditationData::create();
        RegistrarData::create();
        UserData::create();
    }

    public function commit()
    {
        $database = $this->app->make('db');

        foreach ($this->connectionsToTransact() as $name) {
            $database->connection($name)->commit();
        }
    }
}

$obj = new SetupTestCase("test");
$obj->setUp();

if (count($_SERVER["argv"]) > 1 && $_SERVER['argv'][1] == '--clear') {
    $obj->clearAll();
}
$obj->commit();
