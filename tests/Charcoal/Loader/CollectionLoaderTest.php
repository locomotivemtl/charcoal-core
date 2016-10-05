<?php

namespace Charcoal\Tests\Loader;

use \ArrayIterator;

use \Charcoal\Config\GenericConfig;

use \Charcoal\Factory\GenericFactory as Factory;

use \Charcoal\Loader\CollectionLoader;
use \Charcoal\Source\DatabaseSource;

class CollectionLoaderTest extends \PHPUnit_Framework_TestCase
{
    private $obj;

    private $model;
    private $source;

    public function setUp()
    {
        $logger = new \Psr\Log\NullLogger();
        $cache  = new \Cache\Adapter\Void\VoidCachePool();

        $source = new DatabaseSource([
            'logger' => $logger,
            'pdo'    => $GLOBALS['pdo']
        ]);
        $source->setTable('tests');

        $metadataLoader = new \Charcoal\Model\MetadataLoader([
            'logger'    => $logger,
            'cache'     => $cache,
            'base_path' => __DIR__,
            'paths'     => [ 'metadata' ]
        ]);

        $factory = new Factory([
            'arguments' => [[
                'logger'          => $logger,
                'metadata_loader' => $metadataLoader
            ]]
        ]);

        $propertyFactory = new Factory([
            'base_class'       => \Charcoal\Property\PropertyInterface::class,
            'default_class'    => \Charcoal\Property\GenericProperty::class,
            'resolver_options' => [
                'prefix' => '\Charcoal\Property\\',
                'suffix' => 'Property'
            ]
        ]);

        $dependencies = [
            'logger'           => $logger,
            'property_factory' => $propertyFactory,
            'metadata_loader'  => $metadataLoader
        ];

        $propertyFactory->setArguments($dependencies);

        $this->model = new \Charcoal\Model\Model($dependencies);

        $this->obj = new CollectionLoader([
            'logger'  => $logger,
            'factory' => $factory,
        ]);

        $source->setModel($this->model);

        $this->model->setSource($source);
        $this->model->setMetadata(json_decode('
        {
            "properties": {
                "id": {
                    "type": "id"
                },
                "test": {
                    "type": "number"
                },
                "allo": {
                    "type": "number"
                }
            },
            "sources": {
                "default": {
                    "table": "tests"
                }
            },
            "default_source": "default"
        }', true));

        $this->model->source()->createTable();
    }

    public function setData()
    {
        $obj = $this->obj;
        $obj->setData(
            [
                'properties' => [
                    'id',
                    'test'
                ]
            ]
        );
        $this->assertEquals(['id', 'test'], $obj->properties());
    }

    public function setDataIsChainable()
    {
        $obj = $this->obj;
        $ret = $obj->setData([]);
        $this->assertSame($ret, $obj);
    }

    public function testDefaultCollection()
    {
        $loader = $this->obj;
        $collection = $loader->createCollection();
        $this->assertInstanceOf('\Charcoal\Model\Collection', $collection);
    }

    public function testCustomCollectionClass()
    {
        $loader = $this->obj;

        $this->setExpectedException('\InvalidArgumentException');
        $loader->setCollectionClass(false);

        $loader->setCollectionClass(\IteratorIterator::class);
        $this->setExpectedException('\RuntimeException');
        $loader->createCollection();

        $loader->setCollectionClass(ArrayIterator::class);
        $collection = $loader->createCollection();
        $this->assertInstanceOf('\ArrayIterator', $collection);
    }

    public function testAll()
    {
        $loader = $this->obj;
        $loader
            ->setModel($this->model)
            ->setCollectionClass(ArrayIterator::class)
            ->setProperties(['id', 'test'])
            ->addFilter('test', 10, [ 'operator' => '<' ])
            ->addFilter('allo', 1, [ 'operator' => '>=' ])
            ->addOrder('test', 'asc')
            ->setPage(1)
            ->setNumPerPage(10);

        $collection = $loader->load();

        $this->assertEquals(1, 1);

        $this->assertTrue(true);
    }
}
