<?php

namespace Charcoal\Tests\Cache\Memcache;

use \Charcoal\Cache\Memcache\MemcacheCacheConfig as MemcacheCacheConfig;

class MemcacheCacheConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testContructor()
    {
        $obj = new MemcacheCacheConfig();
        $this->assertInstanceOf('\Charcoal\Cache\Memcache\MemcacheCacheConfig', $obj);

        //$this->assertEquals(1, count($obj->servers()));
    }
}
