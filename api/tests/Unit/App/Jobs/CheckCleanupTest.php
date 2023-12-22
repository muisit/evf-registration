<?php

namespace Tests\Unit\App\Jobs;

use App\Models\Event;
use App\Jobs\CheckCleanup;
use App\Jobs\CleanAccreditations;
use App\Support\Services\PDFService;
use Tests\Support\Data\Event as EventData;
use Tests\Unit\TestCase;
use Illuminate\Support\Facades\Queue;

class CheckCleanupTest extends TestCase
{
    public function fixtures()
    {
        EventData::create();
    }

    public function testBasicJob()
    {
        Queue::fake();
        $job = new CheckCleanup();
        $job->handle();
        // event is not finished and not over 30 days ago
        Queue::assertNothingPushed();

        $event = Event::find(EventData::EVENT1);
        $event->event_open = "2000-01-01";
        $event->save();
        $job->handle();
        // storage location does not exist
        @rmdir(PDFService::pdfPath($event));
        Queue::assertPushed(CleanAccreditations::class, 0);

        $event->event_open = "3000-01-01";
        $event->event_config = json_encode(["no_accreditations" => true]);
        $event->save();
        $job->handle();
        Queue::assertPushed(CleanAccreditations::class, 1);
    }

    public function testDateRange()
    {
        $event = Event::find(EventData::EVENT1);
        Queue::fake();
        $job = new CheckCleanup();
        $job->handle();
        // event is not finished and not over 30 days ago
        Queue::assertNothingPushed();

        @mkdir(PDFService::pdfPath($event, 'txt'), 0755, true);
        file_put_contents(PDFService::pdfPath($event, "txt/test.txt"), "This is a test");
        $event->event_open = date('Y-m-d', time() - 29 * 24 * 60 * 60);
        $event->save();
        $job->handle();
        Queue::assertPushed(CleanAccreditations::class, 0);

        $event->event_open = date('Y-m-d', time() - 31 * 24 * 60 * 60);
        $event->save();
        $job->handle();
        Queue::assertPushed(CleanAccreditations::class, 1);
        @unlink(PDFService::pdfPath($event, "txt/test.txt"));
        @rmdir(PDFService::pdfPath($event, "txt"));
        @rmdir(PDFService::pdfPath($event));
    }

    public function testDateRange2()
    {
        $event = Event::find(EventData::EVENT1);
        $event->event_config = json_encode(["no_accreditations" => true]);
        $event->save();
        Queue::fake();
        $job = new CheckCleanup();

        $event->event_open = date('Y-m-d', time() - 2 * 24 * 60 * 60);
        $event->save();
        $job->handle();
        Queue::assertPushed(CleanAccreditations::class, 0);

        $event->event_open = date('Y-m-d', time());
        $event->save();
        $job->handle();
        Queue::assertPushed(CleanAccreditations::class, 1);
    }

    public function testUnique()
    {
        Queue::fake();
        $job = new CheckCleanup();
        dispatch($job);

        $job = new CheckCleanup();
        dispatch($job);

        $job = new CheckCleanup();
        dispatch($job);

        // only one job actually pushed
        Queue::assertPushed(CheckCleanup::class, 1);
    }
}
