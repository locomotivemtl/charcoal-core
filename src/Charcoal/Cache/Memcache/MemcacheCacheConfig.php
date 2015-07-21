<?php

namespace Charcoal\Cache\Memcache;

// Dependencies from `PHP`
use \InvalidArgumentException as InvalidArgumentException;

// Local parent namespace dependencies
use \Charcoal\Cache\CacheConfig as CacheConfig;
// Local namespace dependencies
use \Charcoal\Cache\Memcache\MemcacheCacheServerConfig;

/**
* Memcache Cache Config
*
* Extends the `CacheConfig` class with an available `servers` pool array
*/
class MemcacheCacheConfig extends CacheConfig
{
    /**
    * Pool of available serer, as `MemcacheCacheServerConfig` objects.
    * @var array $_servers
    */
    private $_servers = [];

    /**
    * Default memcache configuration
    *
    * @return array
    */
    public function default_data()
    {
        $default_data = array_merge(
            parent::default_data(),
            [
                'servers' => [
                    [
                        'host'       => 'localhost',
                        'port'       => 11211,
                        'persistent' => false,
                        'weight'     => 1
                    ]
                ]
            ]
        );
        return $default_data;
    }

    /**
    * @param array $data
    * @return MemcacheCacheConfig Chainable
    */
    public function set_data(array $data)
    {
        parent::set_data($data);

        if (isset($data['servers']) && $data['servers'] !== null) {
            $this->set_servers($data['servers']);
        }

        return $this;
    }

    /**
    * @param array $servers
    * @throws InvalidArgumentException if $servers is not an array
    * @return MemcacheCacheConfig Chainable
    */
    public function set_servers(array $servers)
    {
        $this->_servers = [];
        foreach ($servers as $server) {
            $this->add_server($server);
        }
        return $this;
    }

    /**
    * Get the aavilables servers.
    *
    * @return array An array of `MemcacheCacheServerConfig` objects
    */
    public function servers()
    {
        return $this->_servers;
    }

    /**
    * Add a server, from config or array, to the memcache available server pool.
    *
    * @param array|MemcacheCacheServerConfig $server
    * @throws InvalidArgumentException if $server is not a proper array or object
    * @return MemcacheCacheConfig Chainable
    */
    public function add_server($server)
    {
        if (is_array($server)) {
            $server = new MemcacheCacheServerConfig($server);
            $this->_servers[] = $server;
        } elseif (($server instanceof MemcacheCacheServerConfig)) {
            $this->_servers[] = $server;
        } else {
            throw new InvalidArgumentException('Server must be an array or a MemcacheCacheServerConfig object.');
        }
        return $this;
    }
}
