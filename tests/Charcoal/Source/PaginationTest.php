<?php

namespace Charcoal\Tests\Loader\CollectionLoader;

use \Charcoal\Source\Pagination;
use \Charcoal\Charcoal as Charcoal;

class PaginationTest extends \PHPUnit_Framework_TestCase
{
    public function testContructor()
    {
        $obj = new Pagination();
        $this->assertInstanceOf('\Charcoal\Source\Pagination', $obj);

        // Default values
        $this->assertEquals(0, $obj->page());
        $this->assertEquals(0, $obj->num_per_page());
    }

    public function testSetPage()
    {
        $obj = new Pagination();
        $obj->set_page(1);

        $this->assertEquals(1, $obj->page());
    }

    public function testSetPageIsChainable()
    {
        $obj = new Pagination();
        $ret = $obj->set_page(1);

        $this->assertSame($obj, $ret);
    }

    /**
    * @dataProvider providerInvalidParameters
    */
    public function testSetInvalidPageThrowsException($page)
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = new Pagination();
        $obj->set_page($page);
    }

    public function testSetNumPerPage()
    {
        $obj = new Pagination();
        $obj->set_num_per_page(1);

        $this->assertEquals(1, $obj->num_per_page());
    }

    public function testSetNumPerPageIsChainable()
    {
        $obj = new Pagination();
        $ret = $obj->set_num_per_page(1);

        $this->assertSame($obj, $ret);
    }

    /**
    * @dataProvider providerInvalidParameters
    */
    public function testSetInvalidNumPerPageThrowsException($num)
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = new Pagination();
        $obj->set_num_per_page($num);
    }

    public function testFirst()
    {
        $obj = new Pagination();
        $obj->set_page(3);
        $obj->set_num_per_page(20);

        $this->assertEquals(40, $obj->first());
    }

    public function testLast()
    {
        $obj = new Pagination();
        $obj->set_page(3);
        $obj->set_num_per_page(20);

        $this->assertEquals(60, $obj->last());
    }

    /**
    * Invalid arguments for page and num_per_page
    */
    public function providerInvalidParameters()
    {
        $obj = new \StdClass();
        return [
            [''], // empty string is invalid
            [null],
            [true],
            [false],
            [(-1)],
            [[]],
            [['foo']],
            [[1,2,3]],
            [$obj]
        ];
    }
}