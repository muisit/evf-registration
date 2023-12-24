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

    public static function clear()
    {
        self::$booted = [];
    }

    public static function loadAll()
    {
        Fencer::create();
        WPUser::create();
        Registrar::create();

        Event::create();
        Competition::create();
        SideEvent::create();
        EventRole::create();
        AccreditationTemplate::create();

        Registration::create();
        Accreditation::create();
    }
}
