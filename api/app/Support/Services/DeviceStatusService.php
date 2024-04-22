<?php

namespace App\Support\Services;

use App\Models\Competition;
use App\Models\DeviceUser;
use App\Models\DeviceFeed;
use App\Models\Event;
use App\Models\Follow;
use App\Models\Ranking;
use App\Models\Result;
use App\Models\WPPost;
use App\Models\Schemas\BlockStatus;
use App\Models\Schemas\DeviceStatus;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeviceStatusService
{
    public function handle()
    {
        $retval = new DeviceStatus();
        if (Auth::check()) {
            $retval->id = request()->bearerToken() ?? ''; // echo the token to indicate the device was still registered
            $user = Auth::user();
            $retval->feed = $this->getFeedStatus($user);
            $retval->calendar = $this->getCalendarStatus();
            $retval->ranking = $this->getRankingStatus();
            $retval->results = $this->getResultStatus();

            $retval->followers = $this->getFollowers();
            //$retval->following = $this->getFollowing();
        }
        // else the log in device is not registered, we return an empty struct
        return $retval;
    }

    private function getFeedStatus(DeviceUser $user): BlockStatus
    {
        $rows = DB::table(DeviceFeed::tableName())
            ->select([DB::Raw("count(*) as cnt"), DB::Raw("max(updated_at) as last")])
            ->where(function (Builder $query) use ($user) {
                $query->where("device_user_id", $user->getKey())
                    ->orWhereNull('device_user_id');
            })
            ->where('updated_at', '>', Carbon::now()->subYears(2))
            ->get();
        if (count($rows) > 0) {
            return new BlockStatus($rows[0]->cnt ?? 0, $rows[0]->last ?? '');
        }
        return new BlockStatus();
    }

    private function getCalendarStatus(): BlockStatus
    {
        $rows = WPPost::joinRelationshipUsingAlias('meta', 'meta')
            ->select([DB::Raw("count(*) as cnt"), DB::Raw("max(meta.meta_value) as last")])
            ->isEvent()
            ->where('meta.meta_key', '_EventStartDate')
            ->where('meta.meta_value', '>', Carbon::now()->subDays(21)->toDateTimeString())
            ->where('post_type', 'tribe_events')
            ->get();
        if (count($rows) > 0) {
            return new BlockStatus($rows[0]->cnt ?? 0, $rows[0]->last ?? '2024-02-01');
        }
        return new BlockStatus(1, '2024-02-01');
    }

    private function getRankingStatus(): BlockStatus
    {
        $ranking = Ranking::where('id', '>', 0)->orderBy('updated_at', 'desc')->first();
        if (!empty($ranking)) {
            return new BlockStatus($ranking->positions()->count(), $ranking->updated_at);
        }
        return new BlockStatus(0, '');
    }

    private function getResultStatus(): BlockStatus
    {
        $rows = DB::table(Event::tableName())
            ->select([DB::Raw("count(*) as cnt"), DB::Raw("max(event_open) as last")])
            ->whereExists(function (Builder $query) {
                $rt = Result::tableName();
                $query->from($rt)
                  ->join(Competition::tableName() . ' AS c', 'c.competition_id', '=', $rt . '.result_competition')
                  ->whereColumn("c.competition_event", Event::tableName() . '.event_id');
            })
            ->get();

        if (count($rows) > 0) {
            return new BlockStatus($rows[0]->cnt ?? 0, $rows[0]->last ?? '');
        }
        return new BlockStatus();
    }

    private function getFollowers(): array
    {
        // return all fencer uuids that we are following, regardless of blocked state
        $user = Auth::user();
        $lst = Follow::with('fencer')->where('fencer_id', $user->fencer_id ?? 0)->get();
        $retval = [];
        foreach ($lst as $follower) {
            $retval[] = $follower->fencer->uuid;
        }
        return $retval;
    }

    private function getFollowing(): array
    {
        $user = Auth::user();
        $retval = [];
        foreach (Follow::with('fencer')->where('device_user_id', $user->getKey())->get() as $follower) {
            if (!$follower->isBlocked()) {
                \Log::debug("follower preferences " . json_encode($follower->preferences) . " does not include blocked");
                $retval[] = $follower->fencer->uuid;
            }
        }
        return $array;
    }
}
