<?php

namespace Tests\Unit\App\Jobs;

use App\Models\Event;
use App\Jobs\CheckCleanup;
use App\Jobs\CleanAccreditations;
use App\Support\Services\PDFService;
use Tests\Support\Data\Event as EventData;
use Tests\Unit\TestCase;
use Illuminate\Support\Facades\Queue;

class CleanAccreditationsTest extends TestCase
{
    public function fixtures()
    {
        EventData::create();
    }

    public function testBasicJob()
    {
        $event = Event::find(EventData::EVENT1);
        @mkdir(PDFService::pdfPath($event, 'txt'), 0755, true);
        file_put_contents(PDFService::pdfPath($event, "txt/test.txt"), "This is a test");
        $event->event_open = date('Y-m-d', time() - 20 * 24 * 60 * 60);
        $event->save();

        $job = new CleanAccreditations($event);
        $this->assertTrue(file_exists(PDFService::pdfPath($event, "txt/test.txt")));
        $job->handle();
        $this->assertFalse(file_exists(PDFService::pdfPath($event, "txt/test.txt")));
    }

    public function testUnique()
    {
        $event = Event::find(EventData::EVENT1);
        Queue::fake();
        $job = new CleanAccreditations($event);
        dispatch($job);

        $job = new CleanAccreditations($event);
        dispatch($job);

        $job = new CleanAccreditations($event);
        dispatch($job);

        // only one job actually pushed
        Queue::assertPushed(CleanAccreditations::class, 1);
    }
}
