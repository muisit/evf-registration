<?php

namespace App\Support\Services;

use App\Models\Accreditation;
use App\Models\AccreditationDocument;
use App\Models\Competition;
use App\Models\DeviceUser;
use App\Models\DeviceFeed;
use App\Models\Fencer;
use App\Models\Model;
use App\Models\RankingPosition;
use App\Models\Result;
use Carbon\Carbon;

/* Feed Message Service
 *
 * The Feed Message Service generates feed messages related to events of a specific fencer,
 * like registration, badge handout, ranking updates.
 *
 * Non-fencer related feed messages would be generic news posts for example, to which
 * all device users are subscribed by default
 *
 * This service is called in two situations:
 * - creating a feed message for a specific user (you're ranking position is ...)
 * - creating a feed message for followers (:name ranking position is ...)
 *
 * In the first case, the DeviceUser is passed along. If the Fencer does not have a device user,
 * there is no need to generate a personal feed message.
 */

class FeedMessageService
{
    private $localisedTexts = [];
    private $isPersonalMessage = false;
    private $userList = [];
    private ?Fencer $fencer = null;
    private string $type;

    public function generate(Fencer $fencer, Model $object, string $type, ?DeviceUser $user)
    {
        $this->type = $type;
        $this->fencer = $fencer;
        $this->localisedTexts = [];
        // if user is passed along, this message should be personalised for the fencer
        $this->isPersonalMessage = !empty($user) ? true : false;
        $this->userList = $this->getUserList($fencer, $user);
        $this->createLocalisedTexts($object, $type);

        switch ($type) {
            case 'checkin':
                if ($object instanceof AccreditationDocument) {
                    return $this->createCheckinFeed($object);
                }
                break;
            case 'bagstart':
                if ($object instanceof AccreditationDocument) {
                    return $this->createBagStartFeed($object);
                }
                break;
            case 'bagend':
                if ($object instanceof AccreditationDocument) {
                    return $this->createBagEndFeed($object);
                }
                break;
            case 'checkout':
                if ($object instanceof AccreditationDocument) {
                    return $this->createCheckoutFeed($object);
                }
                break;
            case 'handout':
                if ($object instanceof Accreditation) {
                    return $this->createHandoutFeed($object);
                }
                break;
            case 'result':
                if ($object instanceof Result) {
                    return $this->createResultFeed($object);
                }
                break;
            case 'ranking':
                if ($object instanceof RankingPosition) {
                    return $this->createRankingFeed($object);
                }
                break;
            case 'register':
                if ($object instanceof Competition) {
                    return $this->createRegisterFeed($object, false);
                }
                break;
            case 'unregister':
                if ($object instanceof Competition) {
                    return $this->createRegisterFeed($object, true);
                }
                break;
            case 'blocked':
                return $this->createBlockFeed(true);
            case 'unblock':
                return $this->createBlockFeed(false);
            case 'follow':
                return $this->createFollowFeed(true);
            case 'unfollow':
                return $this->createFollowFeed(false);
        }
    }

    private function getUserList(Fencer $fencer, ?DeviceUser $user)
    {
        if (!empty($user)) {
            return [$user];
        }
        else {
            return $fencer->followers()->with('user')->get();
        }
    }

    private function createLocalisedTexts(Model $object, string $type)
    {
        // If this is a personalMessage (you have, you did), the users list contains only
        // one entry
        // There will never be a mix of personal messages and non-personal messages
        // when running this routine
        $title = "";
        $content = "";
        $parameters = [];
        $translatableParameters = [];
        switch ($type) {
            case 'checkin':
                $event = $object->accreditation->event;
                $title = $this->isPersonalMessage
                    ? 'You have submitted your bag'
                    : ':name has submitted their bag';
                $content = $this->isPersonalMessage
                    ? 'You have submitted your material for weapon control at :event'
                    : ':name has submitted their material weapon control at :event';
                $parameters = ['event' => $event->event_title];
                break;
            case 'checkout':
                $event = $object->accreditation->event;
                $title = $this->isPersonalMessage
                    ? 'You have retrieved your bag'
                    : ':name has retrieved their bag';
                $content = $this->isPersonalMessage
                    ? 'You have retrieved your material from weapon control at :event'
                    : ':name has retrieved their material from weapon control at :event';
                $parameters = ['event' => $event->event_title];
                break;
            case 'bagstart':
                $event = $object->accreditation->event;
                $title = $this->isPersonalMessage
                    ? 'Your bag is being processed'
                    : 'The bag of :name is being processed';
                $content = $this->isPersonalMessage
                    ? 'Your material is currently being processed by weapon control at :event'
                    : 'The material of :name is currently being processed by weapon control at :event';
                $parameters = ['event' => $event->event_title];
                break;
            case 'bagend':
                $event = $object->accreditation->event;
                $title = $this->isPersonalMessage
                    ? 'Your bag is available'
                    : 'The bag of :name is available';
                $content = $this->isPersonalMessage
                    ? 'Your material has finished processing and can be retrieved from weapon control at :event'
                    : 'The material of :name has finished processing and can be retrieved from weapon control at :event';
                $parameters = ['event' => $event->event_title];
                break;
            case 'handout':
                $event = $object->accreditation->event;
                $title = $this->isPersonalMessage
                    ? 'You collected your badge'
                    : ':name has collected their badge';
                $content = $this->isPersonalMessage
                    ? 'You have collected your accreditation badge at :event'
                    : ':name has collected their accreditation badge at :event';
                $parameters = ['event' => $event->event_title];
                break;
            case 'result':
                $position = $result->result_place;
                $points = $result->result_total_points;
                $competition = $result->competition;
                $event = $competition->event;
                switch ($position) {
                    case 1:
                        $title = $this->isPersonalMessage
                            ? 'You won :cat :event'
                            : ':name won :cat :event';
                        $content = $this->isPersonalMessage
                            ? 'At :event, you won the competition in :competition'
                            : 'At :event, :name won the competition in :competition';
                        break;
                    case 2:
                        $title = $this->isPersonalMessage
                            ? 'You received silver in :cat'
                            : ':name received silver in :cat';
                        $content = $this->isPersonalMessage
                            ? 'At :event, you received the silver medal in :competition'
                            : 'At :event, :name received the silver medal in :competition';
                        break;
                    case 3:
                        $title = $this->isPersonalMessage
                            ? 'You received bronze in :cat'
                            : ':name received bronze in :cat';
                        $content = $this->isPersonalMessage
                            ? 'At :event, you received the bronze medal in :competition'
                            : 'At :event, :name received the bronze medal in :competition';
                        break;
                    default:
                        $title = $this->isPersonalMessage
                            ? 'You ended up at place :place in :cat'
                            : ':name ended up at place :place in :cat';
                        $content = $this->isPersonalMessage
                            ? 'At :event, you ended up at position :place in :competition'
                            : 'At :event, :name ended up at position :place in :competition';
                        break;
                }
                $parameters = ['event' => $event->event_title];
                $translatableParameters = [
                    'competition' => $competition->title(),
                    'cat' => $competition->weapon->weapon_abbr
                ];
                break;
            case 'ranking':
                $date = Carbon::createFromFormat('Y-m-d', $object->ranking->ranking_date);
                $event = $object->accreditation->event;
                $title = $this->isPersonalMessage
                    ? 'Your ranking position is :position'
                    : ':name holds :position in the ranking';
                $content = $this->isPersonalMessage
                    ? 'Your ranking position in :weapon is :position as of :day :month :year'
                    : ':name holds position :position in :weapon as of :day :month :year';
                $parameters = [
                    'place' => $object->position,
                    'year' => $date->year,
                    'day' => $date->day
                ];
                $translatableParameters = [
                    'weapon' => $object->weapon->weapon_name,
                    'month' => $date->format('F')
                ];
                break;
            case 'register':
                $event = $object->event;
                $weapon = $object->competition->weapon;
                $date = Carbon::createFromFormat("Y-m-d", $object->competition->competition_opens);
                $title = $this->isPersonalMessage
                    ? 'You have registered for :event'
                    : ':name registered for :event';
                $content = $this->isPersonalMessage
                    ? 'You have registered for :weapon at :event on :day :month :year'
                    : ':name has registered for :weapon at :event on :day :month :year';
                $parameters = [
                    'event' => $event->event_title,
                    'year' => $date->year,
                    'day' => $date->day
                ];
                $translatableParameters = [
                    'weapon' => $object->weapon->weapon_name,
                    'month' => $date->format('F')
                ];
                break;
            case 'unregister':
                $event = $object->event;
                $weapon = $object->competition->weapon;
                $date = Carbon::createFromFormat("Y-m-d", $object->competition->competition_opens);
                $title = $this->isPersonalMessage
                    ? 'You have unregistered for :event'
                    : ':name unregistered for :event';
                $content = $this->isPersonalMessage
                    ? 'You have unregistered for :weapon at :event on :day :month :year'
                    : ':name has unregistered for :weapon at :event on :day :month :year';
                $parameters = [
                    'event' => $event->event_title,
                    'year' => $date->year,
                    'day' => $date->day
                ];
                $translatableParameters = [
                    'weapon' => $object->weapon->weapon_name,
                    'month' => $date->format('F')
                ];
                break;
            case 'follow':
                // for Follow and Unfollow, the :name parameter refers to the
                // device-user that is following the Fencer object and the
                // contact flows in a different direction.
                // Because the device-user may not be linked to a fencer,
                // the default is Anonymous
                $name = 'Anonymous';
                if ($this->isPersonalMessage) {
                    if (!empty($object->fencer)) {
                        $name = $object->fencer->getFullName();
                    }
                    // else keep Anonymous
                }
                else if (!$this->isPersonalMessage) {
                    // the user is following a specific fencer, who has a full name
                    $name = $this->fencer->getFullName();
                }
                // notice the 'other-way-around' flow here
                $title = $this->isPersonalMessage
                    ? ':name is following you'
                    : 'You are following :name';
                $content = '';
                $parameters = ["name" => $name];
                break;
            case 'unfollow':
                $name = 'Anonymous';
                if (!empty($object->fencer)) {
                    $name = $object->fencer->getFullName();
                }
                $title = $this->isPersonalMessage
                    ? 'You stopped following :name'
                    : ':name is no longer following you';
                $content = '';
                $parameters = ["name" => $name];
                break;
            case 'block':
                $title = $this->isPersonalMessage
                    ? 'You blocked :name'
                    : ':name blocked you';
                $content = '';
                break;
            case 'unblock':
                $title = $this->isPersonalMessage
                    ? 'You unblocked :name'
                    : ':name unblocked you';
                $content = '';
                break;
        }

        if (!isset($parameters['name'])) {
            $parameters['name'] = $this->fencer->getFullName();
        }

        foreach ($this->userList as $user) {
            $locale = $user->preference['account']['language'] ?? 'en_GB';
            // only use the first part, we do not use dialects etc
            $parts = explode('_', $locale);
            if (sizeof($parts) == 2) {
                $locale = $parts[0];
            }
            if (empty($this->localisedTexts[$locale])) {
                App::setLocale($locale);
                // translate the non-parameterised parameters
                $translated = [];
                foreach ($translatableParameters as $k => $v) {
                    $translated[$k] = __($v);
                }

                // merge regular and translated parameters
                $parameters = array_merge($parameters, $translated);

                // translate title and content using the parameters
                $translatedTitle = __($title, $parameters);
                $translatedContent = __($content, $parameters);
                $this->localisedTexts = (object)[
                    'title' => $translatedTitle,
                    'content' => $translatedContent,
                    'users' => []
                ];
            }
            $this->localisedTexts[$locale]->users[] = $user;
        }
    }

    private function findFeedForContent($doctype, $docid)
    {
        // get a list of feeds with users in the userList for this document type and id
        return DeviceFeed::where('content_id', $docid)
            ->where('content_model', $doctype)
            ->where('fencer_id', $this->fencer->getKey())
            ->users()
            ->where_in('id', $this->userList->map(fn ($usr) => $usr->getKey()))
            ->get();
    }

    private function hashFeedContent(DeviceFeed $feed)
    {
        return md5($feed->locale . $feed->title . $feed->content);
    }

    private function hashFeedType(DeviceFeed $feed)
    {
        return md5($feed->locale . $feed->content_type . '_' . $feed->content_id);
    }

    private function stringTypeToFeedType()
    {
        switch ($this->type) {
            case 'handout':
            case 'checkin':
            case 'bagstart':
            case 'bagend':
            case 'checkout':
            case 'register':
            case 'unregister':
            case 'follow':
            case 'unfollow':
            case 'block':
            case 'unblock':
                return DeviceFeed::NOTIFICATION;
            case 'ranking':
                return DeviceFeed::RANKING;
            case 'result':
                return DeviceFeed::RESULT;
        }
        return DeviceFeed::NOTIFICATION;
    }

    private function createFeedForContent($doctype, $docid, $existingFeeds = [])
    {
        // existingFeeds are passed for the ranking, register, unregister and result feed types
        // In those cases, we may have to overwrite or reuse a given feed
        $feedByType = [];
        foreach ($existingFeeds as $feed) {
            $feedByType[$this->hashFeedType($feed)] = $feed;
        }

        // If there is an existing feed with the exact same content (title, content, locale)
        // then use that feed instead of creating a new one

        foreach ($this->localisedTexts as $locale => $settings) {
            $feed = new DeviceFeed();
            $feed->type = $this->stringTypeToFeedType();
            $feed->fencer_id = $this->fencer->getKey();
            $feed->title = $settings->title;
            $feed->content = $settings->content;
            $feed->locale = $settings->locale;
            $feed->content_id = $docid;
            $feed->content_model = $doctype;
            $feed->created_at = (new Carbon())->toDateTimeString();
            $feed->updated_at = (new Carbon())->toDateTimeString();

            $hashType = $this->hashFeedType($feed);
            // If we already have a feed for this exact type combination, reuse it
            // The content must be overwritten though. It may have changed in
            // one of the parameters (result place, ranking position)
            // For reused register/unregister events, the content probably did not
            // change.
            if (isset($feedByType[$hash])) {
                $newFeed = $feedByType[$hash];
                $newFeed->title = $feed->title;
                $newFeed->content = $feed->content;
                //$newFeed->locale = $feed->locale;
                //$newFeed->content_id = $feed->content_id;
                //$newFeed->content_model = $feed->content_model;
                // $newFeed->created_at = $feed->created_at;
                $newFeed->updated_at = $feed->updated_at;
                $feed = $newFeed;
            }
            else {
                $feed->save();
            }

            // reassign users, even if we are reusing a feed
            // Users that were attached to the feed before but are no longer following the
            // user will be removed from the feed automatically
            $feed->users()->sync(collect($settings->users)->map(fn ($user) => $user->getKey()));
        }
    }

    private function createCheckinFeed(AccreditationDocument $document)
    {
        $existingFeeds = $this->findFeedForContent('checkin', $document->getKey());
        if (!empty($existingFeeds)) {
            // only create one feed for a checkin event, to prevent accidental double clicks or recheckins
            return;
        }
        // send a notification that the bag was checked in for processing
        $this->createFeedForContent('checkin', $document->getKey());
    }

    private function createBagStartFeed(AccreditationDocument $document)
    {
        $existingFeeds = $this->findFeedForContent('bagstart', $document->getKey());
        if (!empty($existingFeeds)) {
            // if a bag was started, but then returned to the queue, we are not sending a cancel message
            // or removing the feed. Just ignore the new event
            return;
        }
        // send a notification that bag processing has started
        $this->createFeedForContent('bagstart', $document->getKey());
    }

    private function createBagEndFeed(AccreditationDocument $document)
    {
        $existingFeeds = $this->findFeedForContent('bagend', $document->getKey());
        if (!empty($existingFeeds)) {
            // if a bag was finished, but then taken back for renewed processing, we do not cancel the feed
            // messages. Instead, just ignore later events
            return;
        }
        // send a notification that bag processing was finished (bag is ready)
        $this->createFeedForContent('bagend', $document->getKey());
    }

    private function createCheckoutFeed(AccreditationDocument $document)
    {
        $existingFeeds = $this->findFeedForContent('checkout', $document->getKey());
        if (!empty($existingFeeds)) {
            // A bag can only ever be checked out once, there is logically no option to have this event more than once
            return;
        }
        // send a notification that the bag was claimed
        $this->createFeedForContent('checkout', $document->getKey());
    }

    private function createHandoutFeed(Accreditation $document)
    {
        // we have no direct option to register that a fencer has already received a handout event for
        // a badge in case they receive two or three badges. Just ignore that user unfriendly aspect
        // and only look at the accreditation
        $existingFeeds = $this->findFeedForContent('handout', $document->getKey());
        if (!empty($existingFeeds)) {
            // a given badge can only ever be handed out once, so don't send out additional messages
            return;
        }
        // send a notification that the accreditation badge was handed out
        $this->createFeedForContent('handout', $document->getKey());
    }

    private function createResultFeed(Result $result)
    {
        // send a notification that a result was achieved
        // - check to see if this feed was already generated
        // - if it was generated, only update the feed message, do not send a notification
        $existingFeeds = $this->findFeedForContent('result', $result->result_competition);
        $this->createFeedForContent('result', $result->result_competition, $existingFeeds);
    }

    private function createRankingFeed(RankingPosition $position)
    {
        // send a notification that a ranking position was updated
        // - check to see if the new ranking position has changed from the most recent one
        // - check to see if this feed was already generated
        // - if it was generated, only update the feed message, do not send a notification
        $existingFeeds = $this->findFeedForContent('ranking', $position->ranking_id);
        $this->createFeedForContent('position', $position->ranking_id, $existingFeeds);
    }

    private function createRegisterFeed(Competition $competition, boolean $wasCancelled)
    {
        // send a notification that someone registered for a competition
        // - check to see if this feed was already registered (in case of a register/cancel/register cycle)
        // - if so, REMOVE the feed about the cancel (leaving only register), send no notification
        //
        // We can use the competition key as model id, because the feed is linked to this specific fencer anyway.
        $existingFeeds = $this->findFeedForContent($wasCancelled ? 'register' : 'unregister', $competition->getKey());
        if ($existingFeeds) {
            $existingFeeds->map(fn($fd) => $fd->delete());
        }
        else {
            $this->createFeedForContent($wasCancelled ? 'register' : 'unregister', $competition->getKey());
        }
    }

    private function createBlockFeed(boolean $wasBlocked)
    {
        // send a notification that someone has blocked a user. This is a very personal action
        // between a specific fencer (that was followed) and a DeviceUser (the follower, who is blocked)
        // Currently, just create new notifications if follow/unfollow messages repeat
        // - check that the block situation actually changed (in case someone repeatedly blocks before processing)
        //
        // localisedTitles should only contain one entry
        $this->createFeedForContent('blocked', $this->fencer->getKey());
    }

    private function createFollowFeed(boolean $isFollowing)
    {
        // send a notification that someone started following a fencer
        // - check that the follow situation actually changed (in case someone repeatedly follows before processing)
        //
        // localisedTitles should only contain one entry
        $this->createFeedForContent('follow', $this->fencer->getKey());
    }
}
