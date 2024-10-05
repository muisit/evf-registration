<?php

namespace Tests\Unit\App\Models\Policies;

use App\Models\Event;
use App\Models\AccreditationUser;
use App\Models\AccreditationDocument;
use App\Models\Policies\AccreditationDocument as Policy;
use Tests\Support\Data\AccreditationUser as AccreditationUserData;
use Tests\Support\Data\AccreditationDocument as AccreditationDocumentData;
use Tests\Support\Data\Event as EventData;
use Tests\Unit\TestCase;
use Carbon\Carbon;

class AccreditationDocumentTest extends TestCase
{
    public function testViewAny()
    {
        request()->merge([
            'eventObject' => Event::where('event_id', EventData::EVENT1)->first()
        ]);

        $policy = new Policy();
        $admin = AccreditationUser::find(AccreditationUserData::ADMIN);
        $accr = AccreditationUser::find(AccreditationUserData::ACCREDITATION);
        $checkin = AccreditationUser::find(AccreditationUserData::CHECKIN);
        $checkout = AccreditationUser::find(AccreditationUserData::CHECKOUT);
        $dt = AccreditationUser::find(AccreditationUserData::DT);
        $mfcat = AccreditationUser::find(AccreditationUserData::MFCAT1); // mfcat is an accreditation user
        $volunteer = AccreditationUser::find(AccreditationUserData::VOLUNTEER); // volunteer is a checkin user

        // only checkin and checkout users can view everything
        $this->assertFalse($policy->viewAny($admin));
        $this->assertFalse($policy->viewAny($accr));
        $this->assertTrue($policy->viewAny($checkin));
        $this->assertTrue($policy->viewAny($checkout));
        $this->assertFalse($policy->viewAny($dt));
        $this->assertFalse($policy->viewAny($mfcat));
        $this->assertTrue($policy->viewAny($volunteer));
    }

    public function testView()
    {
        request()->merge([
            'eventObject' => Event::where('event_id', EventData::EVENT1)->first()
        ]);
        $doc = AccreditationDocument::find(AccreditationDocumentData::MFCAT1);

        $policy = new Policy();
        $admin = AccreditationUser::find(AccreditationUserData::ADMIN);
        $accr = AccreditationUser::find(AccreditationUserData::ACCREDITATION);
        $checkin = AccreditationUser::find(AccreditationUserData::CHECKIN);
        $checkout = AccreditationUser::find(AccreditationUserData::CHECKOUT);
        $dt = AccreditationUser::find(AccreditationUserData::DT);
        $mfcat = AccreditationUser::find(AccreditationUserData::MFCAT1); // mfcat is an accreditation user
        $volunteer = AccreditationUser::find(AccreditationUserData::VOLUNTEER); // volunteer is a checkin user

        // only checkin and checkout users can view
        $this->assertFalse($policy->view($admin, $doc));
        $this->assertFalse($policy->view($accr, $doc));
        $this->assertTrue($policy->view($checkin, $doc));
        $this->assertTrue($policy->view($checkout, $doc));
        $this->assertFalse($policy->view($dt, $doc));
        $this->assertFalse($policy->view($mfcat, $doc));
        $this->assertTrue($policy->view($volunteer, $doc));
    }

    public function testCreate()
    {
        request()->merge([
            'eventObject' => Event::where('event_id', EventData::EVENT1)->first()
        ]);

        $policy = new Policy();
        $admin = AccreditationUser::find(AccreditationUserData::ADMIN);
        $accr = AccreditationUser::find(AccreditationUserData::ACCREDITATION);
        $checkin = AccreditationUser::find(AccreditationUserData::CHECKIN);
        $checkout = AccreditationUser::find(AccreditationUserData::CHECKOUT);
        $dt = AccreditationUser::find(AccreditationUserData::DT);
        $mfcat = AccreditationUser::find(AccreditationUserData::MFCAT1); // mfcat is an accreditation user
        $volunteer = AccreditationUser::find(AccreditationUserData::VOLUNTEER); // volunteer is a checkin user

        // only checkin can create
        $this->assertFalse($policy->create($admin));
        $this->assertFalse($policy->create($accr));
        $this->assertTrue($policy->create($checkin));
        $this->assertFalse($policy->create($checkout));
        $this->assertFalse($policy->create($dt));
        $this->assertFalse($policy->create($mfcat));
        $this->assertTrue($policy->create($volunteer));
    }

    public function testUpdate()
    {
        request()->merge([
            'eventObject' => Event::where('event_id', EventData::EVENT1)->first()
        ]);
        $doc = AccreditationDocument::find(AccreditationDocumentData::MFCAT1);

        $policy = new Policy();
        $admin = AccreditationUser::find(AccreditationUserData::ADMIN);
        $accr = AccreditationUser::find(AccreditationUserData::ACCREDITATION);
        $checkin = AccreditationUser::find(AccreditationUserData::CHECKIN);
        $checkout = AccreditationUser::find(AccreditationUserData::CHECKOUT);
        $dt = AccreditationUser::find(AccreditationUserData::DT);
        $mfcat = AccreditationUser::find(AccreditationUserData::MFCAT1); // mfcat is an accreditation user
        $volunteer = AccreditationUser::find(AccreditationUserData::VOLUNTEER); // volunteer is a checkin user

        // only checkin and checkout users can update
        $this->assertFalse($policy->update($admin, $doc));
        $this->assertFalse($policy->update($accr, $doc));
        $this->assertTrue($policy->update($checkin, $doc));
        $this->assertTrue($policy->update($checkout, $doc));
        $this->assertFalse($policy->update($dt, $doc));
        $this->assertFalse($policy->update($mfcat, $doc));
        $this->assertTrue($policy->update($volunteer, $doc));
    }

    public function testDelete()
    {
        request()->merge([
            'eventObject' => Event::where('event_id', EventData::EVENT1)->first()
        ]);
        $doc = AccreditationDocument::find(AccreditationDocumentData::MFCAT1);

        $policy = new Policy();
        $admin = AccreditationUser::find(AccreditationUserData::ADMIN);
        $accr = AccreditationUser::find(AccreditationUserData::ACCREDITATION);
        $checkin = AccreditationUser::find(AccreditationUserData::CHECKIN);
        $checkout = AccreditationUser::find(AccreditationUserData::CHECKOUT);
        $dt = AccreditationUser::find(AccreditationUserData::DT);
        $mfcat = AccreditationUser::find(AccreditationUserData::MFCAT1); // mfcat is an accreditation user
        $volunteer = AccreditationUser::find(AccreditationUserData::VOLUNTEER); // volunteer is a checkin user

        // only checkin and checkout users can delete
        $this->assertFalse($policy->delete($admin, $doc));
        $this->assertFalse($policy->delete($accr, $doc));
        $this->assertTrue($policy->delete($checkin, $doc));
        $this->assertTrue($policy->delete($checkout, $doc));
        $this->assertFalse($policy->delete($dt, $doc));
        $this->assertFalse($policy->delete($mfcat, $doc));
        $this->assertTrue($policy->delete($volunteer, $doc));
    }
}
