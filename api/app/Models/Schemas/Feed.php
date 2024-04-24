<?php

namespace App\Models\Schemas;

use App\Models\DeviceFeed;

class Feed
{
    public string $id;
    public string $type;
    public string $title;
    public string $content;
    public string $published;
    public string $mutated;
    public string $url;
    public ?string $user;

    public function __construct(DeviceFeed $item)
    {
        $this->id = $item->uuid;
        $this->type = $item->type;
        $this->title = $item->title;
        $this->content = trim($item->content);
        $this->published = $item->created_at;
        $this->mutated = $item->updated_at;
        $this->url = $item->content_url;
        $this->user = $item->user?->uuid;
    }
}
