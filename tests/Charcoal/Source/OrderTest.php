<?php

namespace Charcoal\Tests\Loader\CollectionLoader;

use \Charcoal\Source\Order as Order;
use \Charcoal\Charcoal as Charcoal;

class OrderTest extends \PHPUnit_Framework_TestCase
{

    public function testContructor()
    {
        $obj = new Order();
        $this->assertInstanceOf('\Charcoal\Source\Order', $obj);

        // Default values
        $this->assertEquals('', $obj->property());
        $this->assertEquals('', $obj->mode());
        $this->assertEquals('', $obj->values());
    }

    public function testSetData()
    {
        $obj = new Order();

        $obj->set_data(['property' => 'foo']);
        $this->assertEquals('foo', $obj->property());

        $obj->set_data(
            [
                'property' => 'bar',
                'mode' => 'asc'
            ]
        );
        $this->assertEquals('bar', $obj->property());
        $this->assertEquals('asc', $obj->mode());
    }

    public function testDataIsChainable()
    {
        $obj = new Order();
        $ret = $obj->set_data([]);

        $this->assertSame($ret, $obj);
    }

    public function testSetProperty()
    {
        $obj = new Order();
        $obj->set_property('foo');

        $this->assertEquals('foo', $obj->property());
    }

    public function testSetPropertyIsChainable()
    {
        $obj = new Order();
        $ret = $obj->set_property('foo');

        $this->assertSame($obj, $ret);
    }

    /**
    * @dataProvider providerInvalidProperties
    */
    public function testSetInvalidPropertyThrowsException($property)
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = new Order();
        $obj->set_property($property);
    }

    public function testSetMode()
    {
        $obj = new Order();
        $obj->set_mode('asc');

        $this->assertEquals('asc', $obj->mode());
    }

    public function testSetModeIsChainable()
    {
        $obj = new Order();
        $ret = $obj->set_mode('asc');

        $this->assertSame($obj, $ret);
    }

    /**
    * @dataProvider providerInvalidModes
    */
    public function testSetInvalidModeThrowsException($mode)
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = new Order();
        $obj->set_mode($mode);
    }

    public function testSetValues()
    {
        $obj = new Order();
        $obj->set_values(['foo']);

        $this->assertEquals(['foo'], $obj->values());
    }

    public function testSetValuesByStringExplodesArray()
    {
        $obj = new Order();
        $obj->set_values('foo,bar,val');

        $this->assertEquals(['foo','bar','val'], $obj->values());
    }

    public function testSetValuesByStringTrim()
    {
        $obj = new Order();
        $obj->set_values('foo, bar, val'); // Spaces between values

        $this->assertEquals(['foo','bar','val'], $obj->values());
    }

    public function testSetValuesIsChainable()
    {
        $obj = new Order();
        $ret = $obj->set_values(['foo']);

        $this->assertSame($obj, $ret);
    }

    /**
    * @dataProvider providerInvalidValues
    */
    public function testSetInvalidValuesThrowsException($values)
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = new Order();
        $obj->set_values($values);
    }



    /**
    * Invalid arguments for operator, func and operand
    */
    public function providerInvalidProperties()
    {
        $obj = new \StdClass();
        return [
            [''],
            [null],
            [true],
            [false],
            [1],
            [0],
            [321],
            [[]],
            [['foo']],
            [1,2,3],
            [$obj]
        ];
    }

    /**
    * Invalid arguments for operator, func and operand
    */
    public function providerInvalidModes()
    {
        $obj = new \StdClass();
        return [
            ['invalid string'],
            [''],
            [null],
            [true],
            [false],
            [1],
            [0],
            [321],
            [[]],
            [['foo']],
            [1,2,3],
            [$obj]
        ];
    }

    /**
    * Invalid arguments for operator, func and operand
    */
    public function providerInvalidValues()
    {
        $obj = new \StdClass();
        return [
            [''], // empty strings are invalid
            [null],
            [true],
            [false],
            [1],
            [0],
            [321],
            [[]], // empty arrays are invalid
            [$obj]
        ];
    }

    public function providerAscDesc()
    {
        return [
            ['asc'],
            ['desc']
        ];
    }
}