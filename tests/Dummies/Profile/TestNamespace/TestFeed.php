<?php

namespace NamTenTen\ShortCodes\Test\Dummies\Profile\TestNamespace;

use NamTenTen\ShortCodes\AbstractWidget;

class TestFeed extends AbstractWidget
{
    protected $slides = 6;

    public function run()
    {
        return 'Feed was executed with $slides = '.$this->slides;
    }
}
