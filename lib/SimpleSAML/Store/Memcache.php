<?php

namespace SimpleSAML\Store;

use SimpleSAML\Configuration;
use SimpleSAML\Memcache as _Memcache;
use SimpleSAML\Store;

/**
 * A memcache based data store.
 *
 * @package SimpleSAMLphp
 */
class Memcache extends Store
{
    /**
     * This variable contains the session name prefix.
     *
     * @var string
     */
    private $prefix;


    /**
     * This function implements the constructor for this class. It loads the Memcache configuration.
     */
    protected function __construct()
    {
        $config = Configuration::getInstance();
        $this->prefix = $config->getString('memcache_store.prefix', 'simpleSAMLphp');
    }


    /**
     * Retrieve a value from the data store.
     *
     * @param string $type The data type.
     * @param string $key The key.
     * @return mixed|null The value.
     */
    public function get(string $type, string $key)
    {
        return _Memcache::get($this->prefix.'.'.$type.'.'.$key);
    }


    /**
     * Save a value to the data store.
     *
     * @param string $type The data type.
     * @param string $key The key.
     * @param mixed $value The value.
     * @param int|null $expire The expiration time (unix timestamp), or NULL if it never expires.
     * @return void
     */
    public function set(string $type, string $key, $value, ?int $expire = null) : void
    {
        assert($expire === null || $expire > 2592000);

        if ($expire === null) {
            $expire = 0;
        }

        _Memcache::set($this->prefix.'.'.$type.'.'.$key, $value, $expire);
    }


    /**
     * Delete a value from the data store.
     *
     * @param string $type The data type.
     * @param string $key The key.
     * @return void
     */
    public function delete(string $type, string $key) : void
    {
        _Memcache::delete($this->prefix.'.'.$type.'.'.$key);
    }
}
