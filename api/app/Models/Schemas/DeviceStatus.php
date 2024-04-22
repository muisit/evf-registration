<?php

namespace App\Models\Schemas;

class DeviceStatus
{
    public string $id;
    public BlockStatus $feed;
    public BlockStatus $calendar;
    public BlockStatus $results;
    public BlockStatus $ranking;

    public array $followers;
}
