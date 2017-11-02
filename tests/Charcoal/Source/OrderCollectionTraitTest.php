<?php

namespace Charcoal\Tests\Source;

use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Source\ExpressionInterface;
use Charcoal\Source\Order;
use Charcoal\Source\OrderInterface;
use Charcoal\Source\OrderCollectionTrait;
use Charcoal\Source\OrderCollectionInterface;
use Charcoal\Tests\Mock\OrderCollectionClass;
use Charcoal\Tests\Source\ExpressionCollectionTestTrait;

/**
 * Test {@see OrderCollectionTrait} and {@see OrderCollectionInterface}.
 */
class OrderCollectionTraitTest extends \PHPUnit_Framework_TestCase
{
    use ExpressionCollectionTestTrait;

    /**
     * Create mock object for testing.
     *
     * @return OrderCollectionClass
     */
    final public function createCollector()
    {
        return new OrderCollectionClass();
    }

    /**
     * Create expression for testing.
     *
     * @param  array $data Optional expression data.
     * @return Order
     */
    final protected function createExpression(array $data = null)
    {
        $expr = new Order();
        if ($data !== null) {
            $expr->setData($data);
        }
        return $expr;
    }

    /**
     * Test expression creation from collector.
     *
     * Assertions:
     * 1. Instance of {@see ExpressionInterface}
     * 2. Instance of {@see OrderInterface}
     *
     * @covers \Charcoal\Source\OrderCollectionTrait::createOrder
     */
    public function testCreateExpression()
    {
        $obj = $this->createCollector();

        $result = $this->callMethod($obj, 'createOrder');
        $this->assertInstanceOf(OrderInterface::class, $result);
        $this->assertInstanceOf(ExpressionInterface::class, $result);
    }

    /**
     * Test collection retrieval.
     *
     * Assertions:
     * 1. Empty; Default state
     * 2. Populated; Mutated state
     *
     * @covers \Charcoal\Source\OrderCollectionTrait::orders
     */
    public function testGetExpressions()
    {
        $obj = $this->createCollector();

        /** 1. Default state */
        $ret = $obj->orders();
        $this->assertInternalType('array', $ret);
        $this->assertEmpty($ret);

        /** 2. Mutated state */
        static::setPropertyValue($obj, 'orders', $this->dummyItems);
        $this->assertArrayEquals($this->dummyItems, $obj->orders());
    }

    /**
     * Test collection emptiness.
     *
     * Assertions:
     * 1. Empty; Default state
     * 2. Populated; Mutated state
     *
     * @covers \Charcoal\Source\OrderCollectionTrait::hasOrders
     */
    public function testHasExpressions()
    {
        $obj = $this->createCollector();

        /** 1. Default state */
        $this->assertFalse($obj->hasOrders());

        /** 2. Mutated state */
        static::setPropertyValue($obj, 'orders', $this->dummyItems);
        $this->assertTrue($obj->hasOrders());
    }

    /**
     * Test the mass assignment of expressions.
     *
     * Assertions:
     * 1. Replaces expressions with a new collection
     * 2. Chainable method
     *
     * @covers \Charcoal\Source\OrderCollectionTrait::setOrders
     */
    public function testSetExpressions()
    {
        $obj  = $this->createCollector();
        $exp1 = $this->createExpression();
        $exp2 = $this->createExpression();

        /** 1. Replaces expressions with a new collection */
        static::setPropertyValue($obj, 'orders', $this->dummyItems);
        $this->assertArrayEquals($this->dummyItems, $obj->orders());

        $that = $obj->setOrders([ $exp1, $exp2 ]);
        $ret  = $obj->orders();
        $this->assertCount(2, $ret);
        $this->assertContains($exp1, $ret);
        $this->assertContains($exp2, $ret);

        /** 2. Chainable */
        $this->assertSame($obj, $that);
    }

    /**
     * Test the mass addition of expressions.
     *
     * Assertions:
     * 1. Appends an array of items to the internal collection
     * 2. Chainable method
     *
     * @covers \Charcoal\Source\OrderCollectionTrait::addOrders
     */
    public function testAddExpressions()
    {
        $obj  = $this->createCollector();
        $exp1 = $this->createExpression();
        $exp2 = $this->createExpression();

        /** 1. Appends items to the internal collection */
        static::setPropertyValue($obj, 'orders', $this->dummyItems);
        $this->assertArrayEquals($this->dummyItems, $obj->orders());

        $that = $obj->addOrders([ $exp1, $exp2 ]);
        $ret  = $obj->orders();
        $this->assertCount(5, $ret);
        $this->assertContains($exp1, $ret);
        $this->assertContains($exp2, $ret);

        /** 2. Chainable */
        $this->assertSame($obj, $that);
    }

    /**
     * Test the mass addition of expressions with names.
     *
     * @covers \Charcoal\Source\OrderCollectionTrait::addOrders
     */
    public function testAddExpressionsMap()
    {
        $obj = $this->createCollector();
        $map = [
            'foo' => $this->createExpression(),
            'bar' => $this->createExpression(),
            'qux' => $this->createExpression(),
        ];

        $obj->addOrders($map);
        $ret = $obj->orders();

        $this->assertCount(count($map), $ret);
        $this->assertNotEquals($map, $ret);
        $this->assertArrayContains($map, $ret);

        foreach ($ret as $exp) {
            $this->assertArrayHasKey($exp->name(), $map);
        }
    }

    /**
     * Test the addition of one expression.
     *
     * Assertions:
     * 1. Appends one item to the internal collection
     * 2. Chainable method
     *
     * @covers \Charcoal\Source\OrderCollectionTrait::addOrder
     */
    public function testAddExpression()
    {
        $obj  = $this->createCollector();
        $expr = $this->createExpression();

        /** 1. Appends one item to the internal collection */
        static::setPropertyValue($obj, 'orders', $this->dummyItems);
        $this->assertArrayEquals($this->dummyItems, $obj->orders());

        $that = $obj->addOrder($expr);
        $ret  = $obj->orders();
        $this->assertCount(4, $ret);
        $this->assertContains($expr, $ret);

        /** 2. Chainable */
        $this->assertSame($obj, $that);
    }

    /**
     * Test the parsing of an expression.
     *
     * Assertions:
     * 1. If a string is provided,
     *    an Expression object with a condition is returned
     * 2. If an array is provided,
     *    an Expression object with given data is returned
     * 3. If a closure is provided,
     *    an Expression object is created with the collector's context.
     *
     * @covers \Charcoal\Source\OrderCollectionTrait::processOrder
     */
    public function testProcessExpression()
    {
        $obj = $this->createCollector();

        /** 1. Condition */
        $value  = '`foo` ASC';
        $result = $this->callMethodWith($obj, 'processOrder', $value);
        $this->assertInstanceOf(OrderInterface::class, $result);
        $this->assertEquals($value, $result->condition());

        /** 2. Structure */
        $struct = [
            'name'     => 'foo',
            'property' => 'qux',
        ];
        $result = $this->callMethodWith($obj, 'processOrder', $struct);
        $this->assertInstanceOf(OrderInterface::class, $result);
        $this->assertArrayContains($struct, $result->data());

        /** 3. Closure */
        $lambda = function (OrderInterface $expr, OrderCollectionInterface $tested) use ($struct) {
            return $expr->setData($struct);
        };
        $result = $this->callMethodWith($obj, 'processOrder', $lambda);
        $this->assertInstanceOf(OrderInterface::class, $result);
        $this->assertArrayContains($struct, $result->data());
    }

    /**
     * Test the failure when parsing an invalid expression.
     *
     * @covers \Charcoal\Source\OrderCollectionTrait::processOrder
     */
    public function testProcessExpressionWithInvalidValue()
    {
        $obj = $this->createCollector();

        $this->setExpectedException(InvalidArgumentException::class);
        $this->callMethodWith($obj, 'processOrder', null);
    }
}