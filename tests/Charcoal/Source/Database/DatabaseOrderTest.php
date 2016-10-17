<?php

namespace Charcoal\Tests\Source\Database;

use \Charcoal\Source\Database\DatabaseOrder as DatabaseOrder;

class DatabaseOrderTest extends \PHPUnit_Framework_TestCase
{
    public function testSqlRandMode()
    {
        $obj = new DatabaseOrder();
        $obj->setMode('rand');

        $sql = $obj->sql();
        $this->assertEquals('RAND()', $sql);
    }

    public function testSqlValuesMode()
    {
        $obj = new DatabaseOrder();
        $obj->setMode('values');
        $obj->setProperty('test');
        $obj->setValues('1,2,3');

        $sql = $obj->sql();
        $this->assertEquals('FIELD(`test`, 1,2,3)', $sql);
    }

    public function testSqlValuesModeWithoutPropertyThrowException()
    {
        $this->setExpectedException('\DomainException');

        $obj = new DatabaseOrder();
        $obj->setMode('values');
        $obj->setValues('1,2,3');

        $sql = $obj->sql();
    }

    public function testSqlValuesModeWithoutValuesThrowException()
    {
        $this->setExpectedException('\DomainException');

        $obj = new DatabaseOrder();
        $obj->setMode('values');
        $obj->setProperty('test');

        $sql = $obj->sql();
    }

    /**
     * @dataProvider providerAscDesc
     */
    public function testSqlAscDesc($mode)
    {
        $obj = new DatabaseOrder();
        $obj->setProperty('test');
        $obj->setMode($mode);

        $sql = $obj->sql();
        $this->assertEquals('`test` '.$mode, $sql);
    }

    /**
     * @dataProvider providerAscDesc
     */
    public function testSqlAscDescWithoutPropertyThrowsException($mode)
    {
        $this->setExpectedException('\DomainException');

        $obj = new DatabaseOrder();
        $obj->setMode($mode);

        $sql = $obj->sql();
    }

    public function providerAscDesc()
    {
        return [
            ['asc'],
            ['desc']
        ];
    }
}