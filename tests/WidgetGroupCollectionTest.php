<?php

namespace NamTenTen\ShortCodes\Test;

use NamTenTen\ShortCodes\Test\Support\TestApplicationWrapper;
use NamTenTen\ShortCodes\Test\Support\TestCase;
use NamTenTen\ShortCodes\WidgetGroup;
use NamTenTen\ShortCodes\WidgetGroupCollection;

class WidgetGroupCollectionTest extends TestCase
{
    /**
     * @var WidgetGroupCollection
     */
    protected $collection;

    public function setUp()
    {
        $this->collection = new WidgetGroupCollection(new TestApplicationWrapper());
    }

    public function testItGrantsAccessToWidgetGroup()
    {
        $groupObject = $this->collection->group('sidebar');

        $expectedObject = new WidgetGroup('sidebar', new TestApplicationWrapper());

        $this->assertEquals($expectedObject, $groupObject);
    }
}
