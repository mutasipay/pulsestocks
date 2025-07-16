<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Cache_redis extends CI_Cache_redis
{

    /**
     * Class constructor
     *
     * Setup Redis
     *
     * Loads Redis config file if present. Will halt execution
     * if a Redis connection can't be established.
     *
     * @return  void
     * @see     Redis::connect()
     */
    public function __construct()
    {
        $config = array();
        $CI =& get_instance();

        if ($CI->config->load('redis', TRUE, TRUE))
        {
            $config = $CI->config->item('redis');
        }

        $config = array_merge(self::$_default_config, $config);
        $this->_redis = new Redis();
        try
        {
            if ($config['socket_type'] === 'unix')
            {
                $success = $this->_redis->connect($config['socket']);
            }
            else // tcp socket
            {
                $success = $this->_redis->connect($config['host'], $config['port'], $config['timeout']);
            }

            if ( ! $success)
            {
                log_message('error', 'Cache: Redis connection failed. Check your configuration.');
            }

            if (isset($config['password']) && ! $this->_redis->auth($config['password']))
            {
                log_message('error', 'Cache: Redis authentication failed.');
            }
        }
        catch (RedisException $e)
        {
            log_message('error', 'Cache: Redis connection refused ('.$e->getMessage().')');
        }
        //$this->_redis->setOption(Redis::OPT_SERIALIZER, $this->serializer());
      
        if(isset($config['db_number'])){
            $this->_redis->select($config['db_number']);
        }
    }


    /**
     * Get cache
     *
     * @param   string  Cache ID
     * @return  mixed
     */
    public function get($key)
    {
        return $this->_redis->get($key);
    }

    /**
     * Save cache
     *
     * @param   string  $key    Cache ID
     * @param   mixed   $data   Data to save
     * @param   int $ttl    Time to live in seconds
     * @param   bool    $raw    Whether to store the raw value (unused)
     * @return  bool    TRUE on success, FALSE on failure
     */
    public function save($key, $data, $ttl = 60, $raw = FALSE)
    {
        return $this->_redis->set($key, $data, $ttl);
    }

    /**
     * Delete from cache
     *
     * @param   string  Cache key
     * @return  bool
     */
    public function delete($key)
    {
        if ($this->_redis->delete($key) !== 1)
        {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Returns the serializer constant to use.
     *
     * @return int
     */
    protected function serializer(){
        if (defined('Redis::SERIALIZER_IGBINARY')){
            return Redis::SERIALIZER_IGBINARY;
        }
        return Redis::SERIALIZER_PHP;
    }
}