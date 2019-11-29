<?php

namespace NamTenTen\ShortCodes\Test\Dummies;

use NamTenTen\ShortCodes\AbstractWidget;

class Exception extends AbstractWidget
{
    public function run()
    {
        return 'Exception widget was executed instead of predefined php class';
    }
}
