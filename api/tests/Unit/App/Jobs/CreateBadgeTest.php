<?php

namespace Tests\Unit\App\Jobs;

use App\Models\Accreditation;
use App\Jobs\CreateBadge;
use App\Support\Services\PDFGenerator;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\Registration as RegistrationData;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\EventRole as EventRoleData;
use Tests\Support\Data\SideEvent as SideEventData;
use Tests\Support\Data\Accreditation as AccreditationData;
use Tests\Support\Data\AccreditationTemplate as TemplateData;
use Tests\Unit\TestCase;
use Illuminate\Support\Facades\Queue;
use Laravel\Lumen\Application;

class CreateBadgeTest extends TestCase
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

    public function testBasicJob()
    {
        $sut = $this;
        $this->app->bind(PDFGenerator::class, function (Application $app) use ($sut) {
            $generator = $sut->createMock(PDFGenerator::class);
            $generator->expects($sut->once())->method('generate');
            $generator->expects($sut->once())->method('save');
            return $generator;
        });
        $job = new CreateBadge(Accreditation::find(AccreditationData::MFCAT1));
        $job->handle();
    }

    public function testUnique()
    {
        $accreditation = Accreditation::find(AccreditationData::MFCAT1);
        Queue::fake();
        $job = new CreateBadge($accreditation);
        dispatch($job);

        $job = new CreateBadge($accreditation);
        dispatch($job);

        $job = new CreateBadge($accreditation);
        dispatch($job);

        // only one job actually pushed
        Queue::assertPushed(CreateBadge::class, 1);
    }
}
