<?php

namespace App\Support\Services;

use App\Models\Competition;
use App\Models\DeviceUser;
use App\Models\DeviceFeed;
use App\Models\Event;
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
            return new BlockStatus($rows[0]->cnt ?? 0, $rows[0]->last ?? '');
        }
        return new BlockStatus();
    }

    private function getRankingStatus(): BlockStatus
    {
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
}