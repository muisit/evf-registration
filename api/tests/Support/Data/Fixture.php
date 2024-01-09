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
        Fencer::clear();
        Fencer::create();

        WPUser::clear();
        WPUser::create();

        Registrar::clear();
        Registrar::create();

        Event::clear();
        Event::create();
        Competition::clear();
        Competition::create();
        SideEvent::clear();
        SideEvent::create();
        EventRole::clear();
        EventRole::create();
        AccreditationTemplate::clear();
        AccreditationTemplate::create();

        Registration::clear();
        Registration::create();
        Accreditation::clear();
        Accreditation::create();

        Result::create();
    }
}
