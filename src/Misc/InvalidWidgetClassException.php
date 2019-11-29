<?php

namespace NamTenTen\ShortCodes\Misc;

use Exception;

class InvalidWidgetClassException extends Exception
{
    /**
     * Exception message.
     *
     * @var string
     */
    protected $message = 'ShortCode class must extend NamTenTen\ShortCodes\AbstractShortCode class';
}
