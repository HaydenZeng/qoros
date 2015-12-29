<?php
namespace Common\Redis\Gateway;

use Redis;

class RedisGateway
{
    public $_prefix;
    /**
     * @var Redis
     */
    protected $_redis = null;

    public function __construct($config)
    {
        try {
            $config['host'] = empty($config['host']) ? '127.0.0.1' : $config['host'];
            $config['port'] = empty($config['port']) ? '127.0.0.1' : $config['port'];
            $this->_prefix = empty($config['prefix']) ? 'dang5_' : $config['prefix'];
            $host = $config['host'];
            $port = intval($config['port']);

            //hardcode class_exists('Redis')
            if (!isset($this->_redis) && class_exists('Redis')) {
                $this->_redis = new \Redis();
                $connected = $this->_redis->pconnect($host, $port, 30);
                if (!$connected) {
                    throw new \Exception('connection err');
                }
            }
        } catch (\Exception $e) {
            throw new \Exception('redis connection error' . @$config['host'] . @$config['port'], 9002);
        }
    }

    public function __destruct()
    {
        if (class_exists('Redis')) {
            $this->_redis->close();
        }
    }

    public function select($db)
    {
        $this->_redis->select($db);
    }

    /**
     * @return Redis
     */
    public function getRedis()
    {
        return $this->_redis;
    }

    public function __call($command, $params)
    {
        return call_user_func_array(array($this->_redis, $command), $params);
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->_prefix;
    }
}