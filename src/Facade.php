<?php

namespace NamTenTen\ShortCodes;

class Facade extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor()
    {
        return 'namtenten.shortcode';
    }

    /**
     * Get the widget group object.
     *
     * @param $name
     *
     * @return WidgetGroup
     */
    public static function group($name)
    {
        return app('namtenten.shortcode-group-collection')->group($name);
    }
}
