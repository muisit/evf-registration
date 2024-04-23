<?php

namespace App\Models\Schemas;

class Calendar
{
    public string $id;
    public string $url;
    public string $feed;
    public string $title;
    public string $content;
    public string $location;
    public string $country;
    public string $startDate;
    public string $endDate;
    public string $mutated;
}
