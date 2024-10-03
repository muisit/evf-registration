<?php

namespace Tests\Unit\App\Support\Services;

use App\Models\AccreditationDocument;
use App\Models\Accreditation;
use App\Models\Category;
use App\Models\Competition;
use App\Models\DeviceFeed;
use App\Models\DeviceUser;
use App\Models\Fencer;
use App\Models\Ranking;
use App\Models\RankingPosition;
use App\Models\Result;
use App\Models\Weapon;
use App\Support\Services\FeedMessageService;
use App\Support\Traits\EVFUser;
use Tests\Support\Data\AccreditationUser as AUserData;
use Tests\Support\Data\Accreditation as AccreditationData;
use Tests\Support\Data\AccreditationDocument as AccreditationDocumentData;
use Tests\Support\Data\Competition as CompetitionData;
use Tests\Support\Data\DeviceUser as DeviceUserData;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\Result as ResultData;
use Tests\Unit\TestCase;

class FeedMessageServiceTest extends TestCase
{
    public function testHandoutFeed()
    {
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(5, $feeds);
        $accr = Accreditation::find(AccreditationData::MFCAT1);
        $fencer = Fencer::find(FencerData::MCAT1);

        $service = new FeedMessageService();
        $service->generate($fencer, $accr, 'handout', $fencer->user);
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(6, $feeds);

        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'handout')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(1, count($feed));
        $feed = $feed[0];
        $this->assertEquals('You collected your badge', $feed->title);
        $this->assertEquals('You have collected your accreditation badge at EVF Individual Championships', $feed->content);
        $this->assertEquals(DeviceFeed::NOTIFICATION, $feed->type);
        $this->assertEquals(FencerData::MCAT1, $feed->fencer_id);
        $this->assertEquals(AccreditationData::MFCAT1, $feed->content_id);

        // no new message is generated if one was available
        $service->generate($fencer, $accr, 'handout', $fencer->user);
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'handout')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(1, count($feed));

        // generate for the followers
        $service->generate($fencer, $accr, 'handout');
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(7, $feeds); // one entry for the new locale
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'handout')->where('created_at', '>', '2024-01-01')->orderBy('id')->get();
        $this->assertEquals(2, count($feed));
        $feed = $feed[1];
        $this->assertEquals('Tést De La Teste has collected their badge', $feed->title);
        $this->assertEquals('Tést De La Teste has collected their accreditation badge at EVF Individual Championships', $feed->content);
        $this->assertEquals(DeviceFeed::NOTIFICATION, $feed->type);
        $this->assertEquals(FencerData::MCAT1, $feed->fencer_id);
        $this->assertEquals(AccreditationData::MFCAT1, $feed->content_id);

        // no additional messages generated if we already generated some
        $service->generate($fencer, $accr, 'handout');
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'handout')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(2, count($feed));
    }

    public function testCheckinFeed()
    {
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(5, $feeds);
        $doc = AccreditationDocument::find(AccreditationDocumentData::MFCAT1);
        $fencer = Fencer::find(FencerData::MCAT1);

        $service = new FeedMessageService();
        $service->generate($fencer, $doc, 'checkin', $fencer->user);
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(6, $feeds);

        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'checkin')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(1, count($feed));
        $feed = $feed[0];
        $this->assertEquals('You have submitted your bag', $feed->title);
        $this->assertEquals('You have submitted your material for weapon control at EVF Individual Championships', $feed->content);
        $this->assertEquals(DeviceFeed::NOTIFICATION, $feed->type);
        $this->assertEquals(FencerData::MCAT1, $feed->fencer_id);
        $this->assertEquals($doc->getKey(), $feed->content_id);

        // no new message is generated if one was available
        $service->generate($fencer, $doc, 'checkin', $fencer->user);
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'checkin')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(1, count($feed));

        // generate for the followers
        $service->generate($fencer, $doc, 'checkin');
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(7, $feeds); // one entry for the new locale
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'checkin')->where('created_at', '>', '2024-01-01')->orderBy('id')->get();
        $this->assertEquals(2, count($feed));
        $feed = $feed[1];
        $this->assertEquals('Tést De La Teste has submitted their bag', $feed->title);
        $this->assertEquals('Tést De La Teste has submitted their material weapon control at EVF Individual Championships', $feed->content);
        $this->assertEquals(DeviceFeed::NOTIFICATION, $feed->type);
        $this->assertEquals(FencerData::MCAT1, $feed->fencer_id);
        $this->assertEquals($doc->getKey(), $feed->content_id);

        $service->generate($fencer, $doc, 'checkin');
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'checkin')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(2, count($feed));
    }

    public function testBagstartFeed()
    {
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(5, $feeds);
        $doc = AccreditationDocument::find(AccreditationDocumentData::MFCAT1);
        $fencer = Fencer::find(FencerData::MCAT1);

        $service = new FeedMessageService();
        $service->generate($fencer, $doc, 'bagstart', $fencer->user);
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(6, $feeds);

        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'bagstart')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(1, count($feed));
        $feed = $feed[0];
        $this->assertEquals('Your bag is being processed', $feed->title);
        $this->assertEquals('Your material is currently being processed by weapon control at EVF Individual Championships', $feed->content);
        $this->assertEquals(DeviceFeed::NOTIFICATION, $feed->type);
        $this->assertEquals(FencerData::MCAT1, $feed->fencer_id);
        $this->assertEquals($doc->getKey(), $feed->content_id);

        // no new message is generated if one was available
        $service->generate($fencer, $doc, 'bagstart', $fencer->user);
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'bagstart')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(1, count($feed));

        // generate for the followers
        $service->generate($fencer, $doc, 'bagstart');
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(7, $feeds); // one entry for the new locale
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'bagstart')->where('created_at', '>', '2024-01-01')->orderBy('id')->get();
        $this->assertEquals(2, count($feed));
        $feed = $feed[1];
        $this->assertEquals('The bag of Tést De La Teste is being processed', $feed->title);
        $this->assertEquals('The material of Tést De La Teste is currently being processed by weapon control at EVF Individual Championships', $feed->content);
        $this->assertEquals(DeviceFeed::NOTIFICATION, $feed->type);
        $this->assertEquals(FencerData::MCAT1, $feed->fencer_id);
        $this->assertEquals($doc->getKey(), $feed->content_id);

        $service->generate($fencer, $doc, 'bagstart');
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'bagstart')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(2, count($feed));
    }

    public function testBagendFeed()
    {
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(5, $feeds);
        $doc = AccreditationDocument::find(AccreditationDocumentData::MFCAT1);
        $fencer = Fencer::find(FencerData::MCAT1);

        $service = new FeedMessageService();
        $service->generate($fencer, $doc, 'bagend', $fencer->user);
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(6, $feeds);

        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'bagend')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(1, count($feed));
        $feed = $feed[0];
        $this->assertEquals('Your bag is available', $feed->title);
        $this->assertEquals('Your material has finished processing and can be retrieved from weapon control at EVF Individual Championships', $feed->content);
        $this->assertEquals(DeviceFeed::NOTIFICATION, $feed->type);
        $this->assertEquals(FencerData::MCAT1, $feed->fencer_id);
        $this->assertEquals($doc->getKey(), $feed->content_id);

        // no new message is generated if one was available
        $service->generate($fencer, $doc, 'bagend', $fencer->user);
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'bagend')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(1, count($feed));

        // generate for the followers
        $service->generate($fencer, $doc, 'bagend');
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(7, $feeds); // one entry for the new locale
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'bagend')->where('created_at', '>', '2024-01-01')->orderBy('id')->get();
        $this->assertEquals(2, count($feed));
        $feed = $feed[1];
        $this->assertEquals('The bag of Tést De La Teste is available', $feed->title);
        $this->assertEquals('The material of Tést De La Teste has finished processing and can be retrieved from weapon control at EVF Individual Championships', $feed->content);
        $this->assertEquals(DeviceFeed::NOTIFICATION, $feed->type);
        $this->assertEquals(FencerData::MCAT1, $feed->fencer_id);
        $this->assertEquals($doc->getKey(), $feed->content_id);

        $service->generate($fencer, $doc, 'bagend');
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'bagend')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(2, count($feed));
    }

    public function testCheckoutFeed()
    {
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(5, $feeds);
        $doc = AccreditationDocument::find(AccreditationDocumentData::MFCAT1);
        $fencer = Fencer::find(FencerData::MCAT1);

        $service = new FeedMessageService();
        $service->generate($fencer, $doc, 'checkout', $fencer->user);
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(6, $feeds);

        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'checkout')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(1, count($feed));
        $feed = $feed[0];
        $this->assertEquals('You have retrieved your bag', $feed->title);
        $this->assertEquals('You have retrieved your material from weapon control at EVF Individual Championships', $feed->content);
        $this->assertEquals(DeviceFeed::NOTIFICATION, $feed->type);
        $this->assertEquals(FencerData::MCAT1, $feed->fencer_id);
        $this->assertEquals($doc->getKey(), $feed->content_id);

        // no new message is generated if one was available
        $service->generate($fencer, $doc, 'checkout', $fencer->user);
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'checkout')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(1, count($feed));

        // generate for the followers
        $service->generate($fencer, $doc, 'checkout');
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(7, $feeds); // one entry for the new locale
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'checkout')->where('created_at', '>', '2024-01-01')->orderBy('id')->get();
        $this->assertEquals(2, count($feed));
        $feed = $feed[1];
        $this->assertEquals('Tést De La Teste has retrieved their bag', $feed->title);
        $this->assertEquals('Tést De La Teste has retrieved their material from weapon control at EVF Individual Championships', $feed->content);
        $this->assertEquals(DeviceFeed::NOTIFICATION, $feed->type);
        $this->assertEquals(FencerData::MCAT1, $feed->fencer_id);
        $this->assertEquals($doc->getKey(), $feed->content_id);

        $service->generate($fencer, $doc, 'checkout');
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'checkout')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(2, count($feed));
    }

    public function testResultFeed()
    {
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(5, $feeds);
        $data = Result::find(ResultData::MFCAT1);
        $fencer = Fencer::find(FencerData::MCAT1);

        $service = new FeedMessageService();
        $service->generate($fencer, $data, 'result', $fencer->user);
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(6, $feeds);

        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'result')->whereNot('content_id', null)->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(1, count($feed));
        $feed = $feed[0];
        $this->assertEquals('You won EVF Individual Championships Mens Foil', $feed->title);
        $this->assertEquals('At EVF Individual Championships, you won the competition in Mens Foil Cat 1', $feed->content);
        $this->assertEquals(DeviceFeed::RESULT, $feed->type);
        $this->assertEquals(FencerData::MCAT1, $feed->fencer_id);
        $this->assertEquals($data->result_competition, $feed->content_id);

        // no new message is generated if one was available
        $service->generate($fencer, $data, 'result', $fencer->user);
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'result')->whereNot('content_id', null)->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(1, count($feed));

        // generate for the followers
        $service->generate($fencer, $data, 'result');
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(7, $feeds); // one entry for the new locale
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'result')->whereNot('content_id', null)->where('created_at', '>', '2024-01-01')->orderBy('id')->get();
        $this->assertEquals(2, count($feed));
        $feed = $feed[1];
        $this->assertEquals('Tést De La Teste won EVF Individual Championships Mens Foil', $feed->title);
        $this->assertEquals('At EVF Individual Championships, Tést De La Teste won the competition in Mens Foil Cat 1', $feed->content);
        $this->assertEquals(DeviceFeed::RESULT, $feed->type);
        $this->assertEquals(FencerData::MCAT1, $feed->fencer_id);
        $this->assertEquals($data->result_competition, $feed->content_id);

        $service->generate($fencer, $data, 'result');
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'result')->whereNot('content_id', null)->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(2, count($feed));
    }

    public function testRankingFeed()
    {
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(5, $feeds);
        $ranking = new Ranking();
        $ranking->ranking_date = '2020-01-01';
        $ranking->event_id = EventData::EVENT1;
        $ranking->created_at = '2020-01-01';
        $ranking->updated_at = '2020-01-01';
        $ranking->category_id = Category::CAT1;
        $ranking->weapon_id = Weapon::MF;
        $ranking->save();

        $data = new RankingPosition();
        $data->ranking_id = $ranking->getKey();
        $data->fencer_id = FencerData::MCAT1;
        $data->position = 2;
        $data->points = 127.8;
        $data->settings = [];
        $data->save();

        $fencer = Fencer::find(FencerData::MCAT1);

        $service = new FeedMessageService();
        $service->generate($fencer, $data, 'ranking', $fencer->user);
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(6, $feeds);

        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'ranking')->where('created_at', '>', '2024-01-01')->orderBy('id', 'desc')->get();
        $this->assertEquals(2, count($feed));
        $feed = $feed[0];
        $this->assertEquals('Your ranking position is 2', $feed->title);
        $this->assertEquals('Your ranking position in Mens Foil is 2 as of 1 January 2020', $feed->content);
        $this->assertEquals(DeviceFeed::RANKING, $feed->type);
        $this->assertEquals(FencerData::MCAT1, $feed->fencer_id);
        $this->assertEquals($data->ranking->getKey(), $feed->content_id);

        // no new message is generated if one was available
        $service->generate($fencer, $data, 'ranking', $fencer->user);
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'ranking')->where('created_at', '>', '2024-01-01')->orderBy('id', 'desc')->get();
        $this->assertEquals(2, count($feed));

        // generate for the followers
        $service->generate($fencer, $data, 'ranking');
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(7, $feeds); // one entry for the new locale
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'ranking')->where('created_at', '>', '2024-01-01')->orderBy('id', 'desc')->get();
        $this->assertEquals(3, count($feed));
        $feed = $feed[0];
        $this->assertEquals('Tést De La Teste holds 2 in the ranking', $feed->title);
        $this->assertEquals('Tést De La Teste holds position 2 in Mens Foil as of 1 January 2020', $feed->content);
        $this->assertEquals(DeviceFeed::RANKING, $feed->type);
        $this->assertEquals(FencerData::MCAT1, $feed->fencer_id);
        $this->assertEquals($data->ranking->getKey(), $feed->content_id);

        $service->generate($fencer, $data, 'ranking');
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'ranking')->where('created_at', '>', '2024-01-01')->orderBy('id', 'desc')->get();
        $this->assertEquals(3, count($feed));
    }

    public function testRegisterFeed()
    {
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(5, $feeds);
        $data = Competition::find(CompetitionData::MFCAT1);
        $fencer = Fencer::find(FencerData::MCAT1);

        $service = new FeedMessageService();
        $service->generate($fencer, $data, 'register', $fencer->user);
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(6, $feeds);

        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'register')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(1, count($feed));
        $feed = $feed[0];
        $this->assertEquals('You have registered for EVF Individual Championships', $feed->title);
        $this->assertStringContainsString('You have registered for Mens Foil at EVF Individual Championships on ', $feed->content);
        $this->assertEquals(DeviceFeed::NOTIFICATION, $feed->type);
        $this->assertEquals(FencerData::MCAT1, $feed->fencer_id);
        $this->assertEquals($data->getKey(), $feed->content_id);

        // no new message is generated if one was available
        $service->generate($fencer, $data, 'register', $fencer->user);
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'register')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(1, count($feed));

        // generate for the followers
        $service->generate($fencer, $data, 'register');
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(7, $feeds); // one entry for the new locale
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'register')->where('created_at', '>', '2024-01-01')->orderBy('id')->get();
        $this->assertEquals(2, count($feed));
        $feed = $feed[1];
        $this->assertEquals('Tést De La Teste registered for EVF Individual Championships', $feed->title);
        $this->assertStringContainsString('Tést De La Teste has registered for Mens Foil at EVF Individual Championships on ', $feed->content);
        $this->assertEquals(DeviceFeed::NOTIFICATION, $feed->type);
        $this->assertEquals(FencerData::MCAT1, $feed->fencer_id);
        $this->assertEquals($data->getKey(), $feed->content_id);

        $service->generate($fencer, $data, 'register');
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'register')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(2, count($feed));
    }

    public function testUnregisterFeed()
    {
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(5, $feeds);
        $data = Competition::find(CompetitionData::MFCAT1);
        $fencer = Fencer::find(FencerData::MCAT1);

        $service = new FeedMessageService();
        $service->generate($fencer, $data, 'unregister', $fencer->user);
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(6, $feeds);

        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'unregister')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(1, count($feed));
        $feed = $feed[0];
        $this->assertEquals('You have unregistered for EVF Individual Championships', $feed->title);
        $this->assertStringContainsString('You have unregistered for Mens Foil at EVF Individual Championships on', $feed->content);
        $this->assertEquals(DeviceFeed::NOTIFICATION, $feed->type);
        $this->assertEquals(FencerData::MCAT1, $feed->fencer_id);
        $this->assertEquals($data->getKey(), $feed->content_id);

        // no new message is generated if one was available
        $service->generate($fencer, $data, 'unregister', $fencer->user);
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'unregister')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(1, count($feed));

        // generate for the followers
        $service->generate($fencer, $data, 'unregister');
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(7, $feeds); // one entry for the new locale
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'unregister')->where('created_at', '>', '2024-01-01')->orderBy('id')->get();
        $this->assertEquals(2, count($feed));
        $feed = $feed[1];
        $this->assertEquals('Tést De La Teste unregistered for EVF Individual Championships', $feed->title);
        $this->assertStringContainsString('Tést De La Teste has unregistered for Mens Foil at EVF Individual Championships on ', $feed->content);
        $this->assertEquals(DeviceFeed::NOTIFICATION, $feed->type);
        $this->assertEquals(FencerData::MCAT1, $feed->fencer_id);
        $this->assertEquals($data->getKey(), $feed->content_id);

        $service->generate($fencer, $data, 'unregister');
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'unregister')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(2, count($feed));
    }

    public function testBlockFeed()
    {
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(5, $feeds);
        $user = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        $fencer = Fencer::find(FencerData::MCAT1);

        $service = new FeedMessageService();
        $service->generate($fencer, $user, 'blocked', $fencer->user);
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(6, $feeds);

        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'blocked')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(1, count($feed));
        $feed = $feed[0];
        $this->assertEquals('You blocked John Testita', $feed->title);
        $this->assertEquals('', $feed->content);
        $this->assertEquals(DeviceFeed::NOTIFICATION, $feed->type);
        $this->assertEquals(FencerData::MCAT1, $feed->fencer_id);
        $this->assertEquals(DeviceUserData::DEVICEUSER2, $feed->content_id);

        // a new message is generated every time
        $service->generate($fencer, $user, 'blocked', $fencer->user);
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'blocked')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(2, count($feed));

        // generate for the follower
        $service->generate($fencer, $user, 'blocked');
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(8, $feeds); // two entries for the new locale
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'blocked')->where('created_at', '>', '2024-01-01')->orderBy('id', 'desc')->get();
        $this->assertEquals(3, count($feed));
        $feed = $feed[0];
        $this->assertEquals('Tést De La Teste blocked you', $feed->title);
        $this->assertEquals('', $feed->content);
        $this->assertEquals(DeviceFeed::NOTIFICATION, $feed->type);
        $this->assertEquals(FencerData::MCAT1, $feed->fencer_id);
        $this->assertEquals(DeviceUserData::DEVICEUSER2, $feed->content_id);

        $service->generate($fencer, $user, 'blocked');
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'blocked')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(4, count($feed));

        // Test for the non-fencer-related user
        $user = DeviceUser::find(DeviceUserData::DEVICEUSER3);
        $service->generate($fencer, $user, 'blocked', $fencer->user);
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'blocked')->where('created_at', '>', '2024-01-01')->orderBy('id', 'desc')->get();
        $feed = $feed[0];
        $this->assertEquals('You blocked User' . $user->uuid, $feed->title);
        $this->assertEquals('', $feed->content);
    }

    public function testUnblockFeed()
    {
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(5, $feeds);
        $user = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        $fencer = Fencer::find(FencerData::MCAT1);

        $service = new FeedMessageService();
        $service->generate($fencer, $user, 'unblocked', $fencer->user);
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(6, $feeds);

        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'blocked')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(1, count($feed));
        $feed = $feed[0];
        $this->assertEquals('You unblocked John Testita', $feed->title);
        $this->assertEquals('', $feed->content);
        $this->assertEquals(DeviceFeed::NOTIFICATION, $feed->type);
        $this->assertEquals(FencerData::MCAT1, $feed->fencer_id);
        $this->assertEquals(DeviceUserData::DEVICEUSER2, $feed->content_id);

        // a new message is generated every time
        $service->generate($fencer, $user, 'unblocked', $fencer->user);
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'blocked')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(2, count($feed));

        // generate for the follower
        $service->generate($fencer, $user, 'unblocked');
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(8, $feeds); // two entries for the new locale
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'blocked')->where('created_at', '>', '2024-01-01')->orderBy('id', 'desc')->get();
        $this->assertEquals(3, count($feed));
        $feed = $feed[0];
        $this->assertEquals('Tést De La Teste unblocked you', $feed->title);
        $this->assertEquals('', $feed->content);
        $this->assertEquals(DeviceFeed::NOTIFICATION, $feed->type);
        $this->assertEquals(FencerData::MCAT1, $feed->fencer_id);
        $this->assertEquals(DeviceUserData::DEVICEUSER2, $feed->content_id);

        $service->generate($fencer, $user, 'unblocked');
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'blocked')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(4, count($feed));

        // Test for the non-fencer-related user
        $user = DeviceUser::find(DeviceUserData::DEVICEUSER3);
        $service->generate($fencer, $user, 'unblocked', $fencer->user);
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'blocked')->where('created_at', '>', '2024-01-01')->orderBy('id', 'desc')->get();
        $feed = $feed[0];
        $this->assertEquals('You unblocked User' . $user->uuid, $feed->title);
        $this->assertEquals('', $feed->content);
    }

    public function testFollowFeed()
    {
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(5, $feeds);
        $user = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        $fencer = Fencer::find(FencerData::MCAT1);

        $service = new FeedMessageService();
        $service->generate($fencer, $user, 'follow', $fencer->user);
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(6, $feeds);

        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'follow')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(1, count($feed));
        $feed = $feed[0];
        $this->assertEquals('John Testita is following you', $feed->title);
        $this->assertEquals('', $feed->content);
        $this->assertEquals(DeviceFeed::NOTIFICATION, $feed->type);
        $this->assertEquals(FencerData::MCAT1, $feed->fencer_id);
        $this->assertEquals(DeviceUserData::DEVICEUSER2, $feed->content_id);

        // a new message is generated every time
        $service->generate($fencer, $user, 'follow', $fencer->user);
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'follow')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(2, count($feed));

        // generate for the follower
        $service->generate($fencer, $user, 'follow');
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(8, $feeds); // two entries for the new locale
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'follow')->where('created_at', '>', '2024-01-01')->orderBy('id', 'desc')->get();
        $this->assertEquals(3, count($feed));
        $feed = $feed[0];
        $this->assertEquals('You are following Tést De La Teste', $feed->title);
        $this->assertEquals('', $feed->content);
        $this->assertEquals(DeviceFeed::NOTIFICATION, $feed->type);
        $this->assertEquals(FencerData::MCAT1, $feed->fencer_id);
        $this->assertEquals(DeviceUserData::DEVICEUSER2, $feed->content_id);

        $service->generate($fencer, $user, 'follow');
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'follow')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(4, count($feed));

        // Test for the non-fencer-related user
        $user = DeviceUser::find(DeviceUserData::DEVICEUSER3);
        $service->generate($fencer, $user, 'follow', $fencer->user);
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'follow')->where('created_at', '>', '2024-01-01')->orderBy('id', 'desc')->get();
        $feed = $feed[0];
        $this->assertEquals('User' . $user->uuid . ' is following you', $feed->title);
        $this->assertEquals('', $feed->content);
    }

    public function testUnfollowFeed()
    {
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(5, $feeds);
        $user = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        $fencer = Fencer::find(FencerData::MCAT1);

        $service = new FeedMessageService();
        $service->generate($fencer, $user, 'unfollow', $fencer->user);
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(6, $feeds);

        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'follow')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(1, count($feed));
        $feed = $feed[0];
        $this->assertEquals('John Testita is no longer following you', $feed->title);
        $this->assertEquals('', $feed->content);
        $this->assertEquals(DeviceFeed::NOTIFICATION, $feed->type);
        $this->assertEquals(FencerData::MCAT1, $feed->fencer_id);
        $this->assertEquals(DeviceUserData::DEVICEUSER2, $feed->content_id);

        // a new message is generated every time
        $service->generate($fencer, $user, 'unfollow', $fencer->user);
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'follow')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(2, count($feed));

        // generate for the follower
        $service->generate($fencer, $user, 'unfollow');
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(8, $feeds); // two entries for the new locale
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'follow')->where('created_at', '>', '2024-01-01')->orderBy('id', 'desc')->get();
        $this->assertEquals(3, count($feed));
        $feed = $feed[0];
        $this->assertEquals('You stopped following Tést De La Teste', $feed->title);
        $this->assertEquals('', $feed->content);
        $this->assertEquals(DeviceFeed::NOTIFICATION, $feed->type);
        $this->assertEquals(FencerData::MCAT1, $feed->fencer_id);
        $this->assertEquals(DeviceUserData::DEVICEUSER2, $feed->content_id);

        $service->generate($fencer, $user, 'unfollow');
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'follow')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(4, count($feed));

        // Test for the non-fencer-related user
        $user = DeviceUser::find(DeviceUserData::DEVICEUSER3);
        $service->generate($fencer, $user, 'unfollow', $fencer->user);
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'follow')->where('created_at', '>', '2024-01-01')->orderBy('id', 'desc')->get();
        $feed = $feed[0];
        $this->assertEquals('User' . $user->uuid . ' is no longer following you', $feed->title);
        $this->assertEquals('', $feed->content);
    }

    public function testRegisterUnregisterFeed()
    {
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(5, $feeds);
        $data = Competition::find(CompetitionData::MFCAT1);
        $fencer = Fencer::find(FencerData::MCAT1);

        $service = new FeedMessageService();
        $service->generate($fencer, $data, 'register', $fencer->user);
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'register')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(1, count($feed));

        // no new message is generated if one was available
        $service->generate($fencer, $data, 'register', $fencer->user);
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'register')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(1, count($feed));

        // old message is removed if we unregister
        $service->generate($fencer, $data, 'unregister', $fencer->user);
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'register')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(0, count($feed));
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'unregister')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(0, count($feed));


        // the other way around (theoretical)
        $service->generate($fencer, $data, 'unregister', $fencer->user);
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'unregister')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(1, count($feed));

        // no new message generated
        $service->generate($fencer, $data, 'unregister', $fencer->user);
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'unregister')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(1, count($feed));

        // old message is removed if we register
        $service->generate($fencer, $data, 'register', $fencer->user);
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'register')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(0, count($feed));
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'unregister')->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(0, count($feed));
    }

    public function testRankingFeedUpdate()
    {
        $feeds = DeviceFeed::where('id', '>', 0)->count();
        $this->assertEquals(5, $feeds);
        $ranking = new Ranking();
        $ranking->ranking_date = '2020-01-01';
        $ranking->event_id = EventData::EVENT1;
        $ranking->created_at = '2020-01-01';
        $ranking->updated_at = '2020-01-01';
        $ranking->category_id = Category::CAT1;
        $ranking->weapon_id = Weapon::MF;
        $ranking->save();

        $data = new RankingPosition();
        $data->ranking_id = $ranking->getKey();
        $data->fencer_id = FencerData::MCAT1;
        $data->position = 2;
        $data->points = 127.8;
        $data->settings = [];
        $data->save();

        $fencer = Fencer::find(FencerData::MCAT1);

        $service = new FeedMessageService();
        $service->generate($fencer, $data, 'ranking', $fencer->user);
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'ranking')->where('created_at', '>', '2024-01-01')->orderBy('id','desc')->get();
        $this->assertEquals(2, count($feed));
        $feed = $feed[0];
        $this->assertEquals('Your ranking position is 2', $feed->title);

        // no new message is generated if one was available and is exactly the same
        $service->generate($fencer, $data, 'ranking', $fencer->user);
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'ranking')->where('created_at', '>', '2024-01-01')->orderBy('id','desc')->get();
        $this->assertEquals(2, count($feed));
        $this->assertEquals('Your ranking position is 2', $feed[0]->title);

        $data->position = 3;
        $data->save();
        // it is overriden if the text changed
        $service->generate($fencer, $data, 'ranking', $fencer->user);
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'ranking')->where('created_at', '>', '2024-01-01')->orderBy('id','desc')->get();
        $this->assertEquals(2, count($feed));
        $this->assertEquals('Your ranking position is 3', $feed[0]->title);
    }

    public function testResultFeedUpdate()
    {
        $data = Result::find(ResultData::MFCAT1);
        $fencer = Fencer::find(FencerData::MCAT1);

        $service = new FeedMessageService();
        $service->generate($fencer, $data, 'result', $fencer->user);
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'result')->whereNot('content_id', null)->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(1, count($feed));
        $this->assertEquals('You won EVF Individual Championships Mens Foil', $feed[0]->title);

        // no new message is generated if one was available
        $service->generate($fencer, $data, 'result', $fencer->user);
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'result')->whereNot('content_id', null)->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(1, count($feed));
        $this->assertEquals('You won EVF Individual Championships Mens Foil', $feed[0]->title);

        // it is overriden if the result changed
        $data->result_place = 2;
        $data->save();
        $service->generate($fencer, $data, 'result', $fencer->user);
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'result')->whereNot('content_id', null)->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(1, count($feed));
        $this->assertEquals('You received silver at EVF Individual Championships', $feed[0]->title);

        $data->result_place = 3;
        $data->save();
        $service->generate($fencer, $data, 'result', $fencer->user);
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'result')->whereNot('content_id', null)->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(1, count($feed));
        $this->assertEquals('You received bronze at EVF Individual Championships', $feed[0]->title);
        $this->assertEquals('At EVF Individual Championships, you received the bronze medal in Mens Foil Cat 1', $feed[0]->content);

        $data->result_place = 4;
        $data->save();
        $service->generate($fencer, $data, 'result', $fencer->user);
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'result')->whereNot('content_id', null)->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(1, count($feed));
        $this->assertEquals('You ended up at place 4 at EVF Individual Championships', $feed[0]->title);

        $data->result_place = 5;
        $data->save();
        $service->generate($fencer, $data, 'result', $fencer->user);
        $feed = DeviceFeed::where('locale', 'en')->where('content_model', 'result')->whereNot('content_id', null)->where('created_at', '>', '2024-01-01')->get();
        $this->assertEquals(1, count($feed));
        $this->assertEquals('You ended up at place 5 at EVF Individual Championships', $feed[0]->title);
    }
}
