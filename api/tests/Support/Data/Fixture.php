<?php

namespace Tests\Support\Data;

class Fixture
{
    private static $booted = [];
    protected static function wasBooted($cls)
    {
        return false;
    }

    protected static function booted()
    {
        $cls = get_called_class();
        if (!static::wasBooted($cls)) {
            self::$booted[] = $cls;
        }
    }

    protected static function clear()
    {

    }

    public static function create()
    {
        // get_called_class and static:: use 'late-static-binding'
        // so we bind to the original child class implementation
        if (!static::wasBooted(get_called_class())) {
            static::boot();
        }
    }

    protected static function boot()
    {

    }

    public static function clearBootCache()
    {
        self::$booted = [];
    }

    public static function loadAll()
    {
        Follow::clear();
        DeviceFeed::clear();
        Device::clear();
        DeviceUser::clear();
        WPPost::clear();
        WPOption::clear();
        Result::clear();
        AccreditationDocument::clear();
        AccreditationUser::clear();
        Accreditation::clear();
        Registration::clear();
        AccreditationTemplate::clear();
        EventRole::clear();
        SideEvent::clear();
        Competition::clear();
        Event::clear();
        Registrar::clear();
        WPUser::clear();
        Fencer::clear();

        Fencer::create();
        WPUser::create();
        Registrar::create();
        Event::create();
        Competition::create();
        SideEvent::create();
        EventRole::create();
        AccreditationTemplate::create();
        Registration::create();
        Accreditation::clear(); // clear out any accreditations made due to registration
        Accreditation::create();
        AccreditationUser::create();
        AccreditationDocument::create();
        Result::create();
        WPOption::create();
        WPPost::create();
        DeviceUser::create();
        Device::create();
        DeviceFeed::create();
        Follow::create();
    }
}
