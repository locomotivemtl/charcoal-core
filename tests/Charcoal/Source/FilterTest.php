<?php

namespace Charcoal\Tests\Source;

use InvalidArgumentException;

// From PHPUnit
use PHPUnit_Framework_Error;

// From 'charcoal-core'
use Charcoal\Source\ExpressionInterface;
use Charcoal\Source\Filter;
use Charcoal\Source\FilterInterface;
use Charcoal\Tests\ContainerIntegrationTrait;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Source\ExpressionTestFieldTrait;
use Charcoal\Tests\Source\ExpressionTestTrait;

/**
 * Test {@see Filter} and {@see FilterInterface}.
 */
class FilterTest extends \PHPUnit_Framework_TestCase
{
    use ContainerIntegrationTrait;
    use ExpressionTestFieldTrait;
    use ExpressionTestTrait;
    use ReflectionsTrait;

    /**
     * Create expression for testing.
     *
     * @return Order
     */
    final protected function createExpression()
    {
        return new Filter();
    }

    /**
     * Test new instance.
     *
     * Assertions:
     * 1. Implements {@see FilterInterface}
     */
    public function testFilterConstruct()
    {
        $obj = $this->createExpression();

        /** 1. Implementation */
        $this->assertInstanceOf(FilterInterface::class, $obj);
    }

    /**
     * Test deep cloning of expression trees.
     *
     * @covers \Charcoal\Source\Filter::__clone
     */
    public function testDeepCloning()
    {
        $obj = $this->createExpression();
        $obj->addFilters([
            [
                'condition' => 'title LIKE "Hello %"'
            ],
            [
                'property' => 'trashed',
                'operator' => 'IS NULL'
            ],
            [
                'property' => 'author_id',
                'value'    => 1
            ]
        ]);

        $cln = clone $obj;
        $this->assertEquals($cln, $obj);
        $this->assertNotSame($cln, $obj);

        $originals = $obj->filters();
        foreach ($cln->filters() as $i => $dupe) {
            $this->assertNotSame($originals[$i], $dupe);
        }
    }

    /**
     * Provide data for value parsing.
     *
     * @used-by ExpressionTestTrait::testDefaultValues()
     * @return  array
     */
    final public function provideDefaultValues()
    {
        return [
            'property'    => [ 'property',     null ],
            'table'       => [ 'table',        null ],
            'value'       => [ 'value',        null ],
            'function'    => [ 'func',         null ],
            'operator'    => [ 'operator',     '=' ],
            'conjunction' => [ 'conjunction',  'AND' ],
            'filters'     => [ 'filters',      [] ],
            'condition'   => [ 'condition',    null ],
            'active'      => [ 'active',       true ],
            'name'        => [ 'name',         null ],
        ];
    }

    /**
     * Test the "value" property.
     *
     * Assertions:
     * 1. Default state
     * 2. Mutated state
     * 3. Chainable method
     *
     * Note: {@see Filter::value()} uses {@see \Charcoal\Source\AbstractExpression::parseValue()}.
     * Tests for `parseValue()` are performed in {@see ExpressionTestTrait::testParseValue()}.
     */
    public function testValue()
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertNull($obj->value());

        /** 2. Mutated Value */
        $that = $obj->setValue('foobar');
        $this->assertInternalType('string', $obj->value());
        $this->assertEquals('foobar', $obj->value());

        /** 3. Chainable */
        $this->assertSame($obj, $that);
    }

    /**
     * Test deprecated "val" property.
     */
    public function testDeprecatedValExpression()
    {
        $obj = $this->createExpression();

        @$obj->setData([ 'val' => 'qux' ]);
        $this->assertEquals('qux', $obj->value());
    }

    /**
     * Test "val" property deprecation notice.
     */
    public function testDeprecatedValError()
    {
        $this->setExpectedException(PHPUnit_Framework_Error::class);
        $this->createExpression()->setData([ 'val' => 'qux' ]);

    }

    /**
     * Test the "operator" property.
     *
     * Assertions:
     * 1. Default state
     * 2. Mutated state
     * 3. Chainable method
     * 4. Accepts mixed case
     */
    public function testOperator()
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertEquals('=', $obj->operator());

        /** 2. Mutated Value */
        $that = $obj->setOperator('LIKE');
        $this->assertInternalType('string', $obj->operator());
        $this->assertEquals('LIKE', $obj->operator());

        /** 3. Chainable */
        $this->assertSame($obj, $that);

        /** 4. Accepts mixed case */
        $obj->setOperator('is null');
        $this->assertEquals('IS NULL', $obj->operator());
    }

    /**
     * Test "operator" property with unsupported operator.
     */
    public function testOperatorWithUnsupportedOperator()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->createExpression()->setOperator('foo');
    }

    /**
     * Test "operator" property with invalid value.
     */
    public function testOperatorWithInvalidValue()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->createExpression()->setOperator(42);
    }

    /**
     * Test the "func" property.
     *
     * Assertions:
     * 1. Default state
     * 2. Mutated state
     * 3. Chainable method
     * 4. Accepts mixed case
     * 5. Accepts NULL
     */
    public function testFunc()
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertNull($obj->func());

        /** 2. Mutated Value */
        $that = $obj->setFunc('LENGTH');
        $this->assertInternalType('string', $obj->func());
        $this->assertEquals('LENGTH', $obj->func());

        /** 3. Chainable */
        $this->assertSame($obj, $that);

        /** 4. Accepts mixed case */
        $obj->setFunc('weekDay');
        $this->assertEquals('WEEKDAY', $obj->func());

        /** 5. Accepts NULL */
        $obj->setFunc(null);
        $this->assertNull($obj->func());
    }

    /**
     * Test "func" property with unsupported func.
     */
    public function testFuncWithUnsupportedFunction()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->createExpression()->setFunc('xyzzy');
    }

    /**
     * Test "func" property with invalid value.
     */
    public function testFuncWithInvalidValue()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->createExpression()->setFunc(33);
    }

    /**
     * Test the "conjunction" property.
     *
     * Assertions:
     * 1. Default state
     * 2. Mutated state
     * 3. Chainable method
     * 4. Accepts mixed case
     */
    public function testConjunction()
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertEquals('AND', $obj->conjunction());

        /** 2. Mutated Value */
        $that = $obj->setConjunction('||');
        $this->assertInternalType('string', $obj->conjunction());
        $this->assertEquals('||', $obj->conjunction());

        /** 3. Chainable */
        $this->assertSame($obj, $that);

        /** 4. Accepts mixed case */
        $obj->setConjunction('xor');
        $this->assertEquals('XOR', $obj->conjunction());
    }

    /**
     * Test "conjunction" property with unsupported conjunction.
     */
    public function testConjunctionWithUnsupportedConjunction()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->createExpression()->setConjunction('qux');
    }

    /**
     * Test "conjunction" property with invalid value.
     */
    public function testConjunctionWithInvalidValue()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->createExpression()->setConjunction(11);
    }

    /**
     * Test deprecated "operand" property.
     */
    public function testDeprecatedOperandExpression()
    {
        $obj = $this->createExpression();

        @$obj->setData([ 'operand' => 'XOR' ]);
        $this->assertEquals('XOR', $obj->conjunction());
    }

    /**
     * Test "operand" property deprecation notice.
     */
    public function testDeprecatedOperandError()
    {
        $this->setExpectedException(PHPUnit_Framework_Error::class);
        $this->createExpression()->setData([ 'operand' => 'XOR' ]);
    }

    /**
     * Test implementation of {@see Countable}.
     *
     * Assertions:
     * 1. Default state
     * 2. Mutated state
     *
     * @covers \Charcoal\Source\Filter::count
     */
    public function testCount()
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertEquals(0, $obj->count());

        /** 2. Mutated Value */
        $obj->addFilter('1 = 1');
        $this->assertEquals(1, $obj->count());
    }

    /**
     * Test the creation of a query filter expression.
     *
     * Assertions:
     * 1. Instance of {@see ExpressionInterface}
     * 2. Instance of {@see Filter}
     *
     * @see    \Charcoal\Tests\Source\AbstractSourceTest::testCreateFilter
     * @covers \Charcoal\Source\Filter::createFilter
     */
    public function testCreateFilter()
    {
        $obj = $this->createExpression();

        $result = $this->callMethodWith($obj, 'createFilter', [ 'name' => 'foo' ]);
        $this->assertInstanceOf(Filter::class, $result);
        $this->assertInstanceOf(ExpressionInterface::class, $result);
        $this->assertEquals('foo', $result->name());
    }

    /**
     * Test data structure with mutated state.
     *
     * Assertions:
     * 1. Mutate all options
     * 2. Partially mutated state
     * 3. Mutation via aliases
     */
    public function testData()
    {
        /** 1. Mutate all options */
        $exp1 = $this->createExpression();

        $mutation = [
            'value'       => '%foobar',
            'func'        => 'REVERSE',
            'operator'    => 'LIKE',
            'property'    => 'col',
            'table'       => 'tbl',
            'conjunction' => 'OR',
            'filters'     => [ 'foo' => $exp1 ],
            'condition'   => '1 = 1',
            'active'      => false,
            'name'        => 'foo',
        ];

        $obj = $this->createExpression();
        $obj->setData($mutation);
        $this->assertStructHasBasicData($obj, $mutation);
        $this->assertStructHasFieldData($obj, $mutation);

        $data = $obj->data();

        $this->assertArrayHasKey('value', $data);
        $this->assertEquals('%foobar', $data['value']);
        $this->assertEquals('%foobar', $obj->value());

        $this->assertArrayHasKey('func', $data);
        $this->assertEquals('REVERSE', $data['func']);
        $this->assertEquals('REVERSE', $obj->func());

        $this->assertArrayHasKey('operator', $data);
        $this->assertEquals('LIKE', $data['operator']);
        $this->assertEquals('LIKE', $obj->operator());

        $this->assertArrayHasKey('conjunction', $data);
        $this->assertEquals('OR', $data['conjunction']);
        $this->assertEquals('OR', $obj->conjunction());

        $this->assertArrayHasKey('filters', $data);
        $this->assertContains($exp1, $data['filters']);
        $this->assertContains($exp1, $obj->filters());

        /** 2. Partially mutated state */
        $mutation = [
            'operator' => 'IS NULL'
        ];

        $obj = $this->createExpression();
        $obj->setData($mutation);

        $defs = $obj->defaultData();
        $this->assertStructHasBasicData($obj, $defs);

        $this->assertEquals($defs['value'], $obj->value());
        $this->assertEquals($defs['func'], $obj->func());
        $this->assertEquals($defs['conjunction'], $obj->conjunction());
        $this->assertEquals($defs['condition'], $obj->condition());

        $data = $obj->data();
        $this->assertNotEquals($defs['operator'], $data['operator']);
        $this->assertEquals('IS NULL', $data['operator']);

        /** 3. Mutation via aliases */
        $exp2 = $this->createExpression();

        $mutation = [
            'function'   => 'REVERSE',
            'conditions' => [ 'baz' => $exp2 ]
        ];

        $obj = $this->createExpression();
        $obj->setData($mutation);

        $data = $obj->data();
        $this->assertEquals('REVERSE', $data['func']);
        $this->assertContains($exp2, $data['filters']);
    }

    /**
     * Test deprecated "string" property.
     *
     * @see OrderTest::testDeprecatedStringExpression()
     */
    public function testDeprecatedStringExpression()
    {
        $obj = $this->createExpression();

        @$obj->setData([ 'string' => '1 = 1' ]);
        $this->assertEquals('1 = 1', $obj->condition());
    }

    /**
     * Test "string" property deprecation notice.
     *
     * @see OrderTest::testDeprecatedStringError()
     */
    public function testDeprecatedStringError()
    {
        $this->setExpectedException(PHPUnit_Framework_Error::class);
        $this->createExpression()->setData([ 'string' => '1 = 1' ]);

    }
}
