<?php

namespace Tests\Support\Data;

class Fixture
{
    private static $booted = [];
    protected static function wasBooted($cls)
    {
        return in_array($cls, self::$booted);
    }

    protected static function booted()
    {
        $cls = get_called_class();
        if (!self::wasBooted($cls)) {
            self::$booted[] = $cls;
        }
    }

    public static function create()
    {
        // get_called_class and static:: use 'late-static-binding'
        // so we bind to the original child class implementation
        if (!self::wasBooted(get_called_class())) {
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
}
