<?php

namespace NamTenTen\ShortCodes;

class AsyncFacade extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor()
    {
        return 'arrilot.async-widget';
    }
}
