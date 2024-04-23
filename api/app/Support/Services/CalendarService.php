<?php

namespace App\Support\Services;

use App\Models\WPPost;
use App\Models\WPPostMeta;
use App\Models\Schemas\Calendar;
use Carbon\Carbon;

class CalendarService
{
    public function generate()
    {
        $mt = WPPostMeta::tableName();
        $events = WPPost::joinRelationship('meta', fn ($join) => $join->where($mt . '.meta_key', '_EventEndDateUTC'))
            ->isEvent()
            ->where($mt . '.meta_value', '>', Carbon::now()->toDateString())
            ->get();

        $retval = [];
        foreach ($events as $event) {
            $meta = WPPostMeta::where('post_id', $event->ID)->get()->keyBy('meta_key');
            $calendar = new Calendar();
            $calendar->id = $event->ID;
            $calendar->startDate = (new Carbon($this->safeMeta($meta, '_EventStartDateUTC')))->toDateString();
            $calendar->endDate = (new Carbon($this->safeMeta($meta, '_EventEndDateUTC')))->toDateString();

            $venue = $this->getPostWithMeta($this->safeMeta($meta, '_EventVenueID'));
            $calendar->country = $venue->_VenueCountry;
            $calendar->location = $venue->_VenueCity;

            $calendar->url = $this->safeMeta($meta, '_EventURL');
            $calendar->feed = $this->safeMeta($meta, 'event_feed');

            $calendar->title = $event->post_title;
            $calendar->content = strip_tags(str_replace(["<br/>","<br>","<br />"], "\r\n", $event->post_content));
            $calendar->mutated = $event->post_modified_gmt;
            $retval[] = $calendar;
        }
        return $retval;
    }

    private function getPostWithMeta($id)
    {
        $post = WPPost::where('ID', $id)->first();
        $meta = WPPostMeta::where('post_id', $post->ID)->get()->keyBy('meta_key');
        foreach ($meta as $key => $value) {
            $post->{$key} = $value['meta_value'];
        }
        return $post;
    }

    private function safeMeta($meta, $key) {
        return ($meta[$key] ?? [])['meta_value'] ?? '';
    }
}