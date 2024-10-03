<?php

namespace Tests\Unit\App\Models;

use App\Models\Accreditation;
use App\Models\AccreditationDocument;
use App\Models\Event;
use Tests\Support\Data\Accreditation as AccreditationData;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\AccreditationDocument as DocData;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\Unit\TestCase;

class AccreditationDocumentTest extends TestCase
{
    public function testRelations()
    {
        $data = AccreditationDocument::where('id', DocData::MFCAT1)->first();
        $this->assertNotEmpty($data);
        $this->assertEquals($data->card, '21');
        $this->assertEquals($data->document_nr, '88');
        $this->assertEquals('{}', $data->payload);
        $this->assertEquals(AccreditationDocument::STATUS_CREATED, $data->status);
        $this->assertEquals("2021-01-01 01:00:00", $data->checkin);
        $this->assertEmpty($data->process_start);
        $this->assertEmpty($data->process_end);
        $this->assertEmpty($data->checkout);
        $this->assertEmpty($data->checkout_badge);

        $this->assertInstanceOf(BelongsTo::class, $data->accreditation());
        $this->assertInstanceOf(Accreditation::class, $data->accreditation);
        $this->assertEquals(AccreditationData::MFCAT1, $data->accreditation->getKey());

        $this->assertInstanceOf(BelongsTo::class, $data->creator());
        $this->assertInstanceOf(BelongsTo::class, $data->updator());
    }

    public function testSave()
    {
        $data = new AccreditationDocument();
        $data->accreditation_id = AccreditationData::MFCAT1;
        $data->status = AccreditationDocument::STATUS_CREATED;
        $data->checkin = '2000-01-01 01:02:34';
        $this->assertEmpty($data->created_at);
        $this->assertEmpty($data->updated_at);

        $data->save();
        $this->assertNotEmpty($data->created_at);
        $this->assertNotEmpty($data->updated_at);

        sleep(1);
        $data->checkin = '2001-01-01 00:00:00';
        $data->save();
        $this->assertNotEmpty($data->created_at);
        $this->assertNotEmpty($data->updated_at);
        $this->assertNotEquals($data->created_at, $data->updated_at);
    }
}
