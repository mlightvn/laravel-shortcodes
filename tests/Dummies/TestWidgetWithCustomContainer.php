<?php

namespace NamTenTen\ShortCodes\Test\Dummies;

use NamTenTen\ShortCodes\AbstractWidget;

class TestWidgetWithCustomContainer extends AbstractWidget
{
    public $reloadTimeout = 10;

    public function run()
    {
        return 'Dummy Content';
    }

    public function placeholder()
    {
        return 'Placeholder here!';
    }

    /**
     * Async and reloadable widgets are wrapped in container.
     * You can customize it by overwriting this method.
     *
     * @return array
     */
    public function container()
    {
        return [
            'element'       => 'p',
            'attributes'    => 'data-id="123"',
        ];
    }
}
