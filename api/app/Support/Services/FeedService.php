<?php

namespace App\Support\Services;

use App\Models\DeviceUser;
use App\Models\DeviceFeed;
use App\Support\Contracts\EVFUser;
use App\Models\Schemas\Feed;
use Carbon\Carbon;

class FeedService
{
    public function generate(EVFUser $user)
    {
        if (! ($user instanceof DeviceUser)) {
            \Log::debug("not generating feed for non-device user");
            return [];
        }

        // return all feed items of the last 2 years
        // If this becomes too much, we will have to look at paging
        $items = $user->feeds()
            ->where('created_at', '>', Carbon::now()->subYears(2))
            ->orderBy('created_at', 'desc')->get();
        $retval = [];
        foreach ($items as $item) {
            $retval[] = new Feed($item);
        }
        return $retval;
    }
}
