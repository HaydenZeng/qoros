<?php

namespace Common\Redis;

use Common\Redis\Gateway\RedisGateway;

class Redis {
    /**
     *
     * @var RedisGateway
     */
    protected $_redis = NULL;
    protected $_prefix;
    
    /**
     * redis计数器的key段位
     * 段位1：域
     * 段位2：模块
     * 段位3：逻辑名 (recusive)
     * 段位4：属性
     * 
     * @var string
     */
    private $segments;
    // 不要随意更改下面元素的顺序
    private $scheme = array (
            'default',
            'site'
    );
    private $_holde = false;
    public function __construct(RedisGateway $redisGateway) {
        $this->_redis = $redisGateway;
        $this->_prefix = $this->_redis->_prefix;
    }
    
    /**
     *
     * @return assembled redis key
     */
    public function key() {
        return $this->assembleKey ();
    }
    
    /**
     *
     * @deprecated
     *
     * @return Redis
     */
    public function getRedis() {
        return $this->_redis->getRedis ();
    }
    
    /**
     * 域范围
     * 
     * @param string $region            
     * @return RedisModel
     */
    protected function region($region = 'national') {
        $this->segments ['region'] = $region;
        return $this;
    }
    
    /**
     * 模块范围
     * 
     * @param string $module            
     * @param string $param            
     * @return RedisModel
     */
    protected function module($module = 'eck', $param = null) {
        $this->segments ['module'] = array (
                $module => $param 
        );
        return $this;
    }
    
    /**
     * 逻辑称呼 可以被循环调用
     * recursive
     * 
     * @param string $logic            
     * @param string $param            
     * @return RedisModel
     */
    protected function logic($logic, $param = null) {
        if (empty ( $this->segments ['module'] )) {
            $this->segments ['module'] = array (
                    'eck' => null 
            );
        }
        $this->segments ['logic'] [$logic] = $param;
        return $this;
    }
    
    /**
     * 对象属性
     * 
     * @param string $property            
     * @param string $param            
     * @return RedisModel
     */
    protected function property($property) {
        if (empty ( $this->segments ['logic'] )) {
            throw new \Exception ( 'logic name is not set' );
        }
        $this->segments ['property'] = $property;
        return $this;
    }
    
    /**
     * 重置$segments
     * 
     * @return RedisModel
     */
    protected function reset() {
        if (! $this->_holde) {
            $this->segments = array ();
        }
        return $this;
    }
    
    /**
     * n秒后过期
     * 
     * @param int $seconds            
     */
    protected function expire($seconds) {
        $this->_redis->expire ( $this->assembleKey (), $seconds );
    }
    
    /**
     * 当在unix时间戳$timestamp过期
     * 
     * @param
     *            unix timestamp $timestamp
     */
    protected function expireAt($timestamp) {
        $this->_redis->expireAt ( $this->assembleKey (), $timestamp );
    }
    
    /**
     * 增加计数
     * 
     * @param string $member
     *            元素值
     * @param int $value
     *            计数
     */
    protected function incr($member = null, $value = 1) {
        try {
            $key = $this->assembleKey ();
            // hash 类型
            if (isset ( $this->segments ['property'] )) {
                $ret = $this->_redis->hIncrBy ( $key, $this->segments ['property'], $value );
            } else {
                if ($member == null) {
                    $stringFunc = ($value < 0) ? 'decr' : 'incr';
                    // 键值对类型
                    $ret = $this->_redis->{$stringFunc} ( $key, $value );
                } else {
                    // 集合类型
                    $ret = $this->_redis->zIncrBy ( $key, $value, $member );
                }
            }
            $this->reset ();
            return $ret;
        } catch ( \Exception $e ) {
            echo "RedisModel: INCR " . $this->debug;
        }
    }
    protected function zAdd($score, $member) {
        try {
            $key = $this->assembleKey ();
            $ret = $this->_redis->zAdd ( $key, $score, $member );
            $this->reset ();
            return $ret;
        } catch ( \Exception $e ) {
            echo "RedisModel: zAdd " . $this->debug;
        }
    }
    
    /**
     * 减少计数
     * 
     * @param string $member
     *            元素值
     * @param int $value
     *            计数
     */
    protected function decr($member = null, $value = 1) {
        $ret = $this->incr ( $member, - $value );
        $this->reset ();
        return $ret;
    }
    
    /**
     * 删除计数
     * 
     * @param string $member            
     */
    protected function del($member = null) {
        try {
            $key = $this->assembleKey ();
            if ($member == null) {
                $ret = $this->_redis->del ( $key );
            } else {
                $ret = $this->_redis->zRem ( $key, $member );
            }
            $this->reset ();
            return $ret;
        } catch ( \Exception $e ) {
            echo "RedisModel: DEL " . $this->debug;
        }
    }
    
    /**
     * 取成员计数
     * 
     * @param string $member            
     */
    protected function count($member = null) {
        try {
            $key = $this->assembleKey ();
            // hash 类型
            if (isset ( $this->segments ['property'] )) {
                $ret = $this->_redis->hGet ( $key, $this->segments ['property'] );
            } else {
                if ($member == null) {
                    // 键值对类型
                    $ret = $this->_redis->get ( $key );
                } else {
                    // 集合类型
                    $ret = $this->_redis->zScore ( $key, $member );
                }
            }
            $this->reset ();
            return intval ( $ret );
        } catch ( \Exception $e ) {
            echo "RedisModel: COUNT " . $this->debug;
        }
    }
    
    /**
     *
     * @return array(field=>value)
     */
    protected function countAll() {
        $key = $this->assembleKey ();
        $ret = $this->_redis->hGetAll ( $key );
        $this->reset ();
        return $ret;
    }
    
    /**
     * 根据计数区间查询
     * 
     * @param
     *            mixed string or int $min
     * @param
     *            mixed string or int $max
     * @param array $option            
     */
    protected function rangeByScore($min, $max, $option = null) {
        $key = $this->assembleKey ();
        $ret = $this->_redis->zRangeByScore ( $key, $min, $max, $option );
        $this->reset ();
        return $ret;
    }
    
    /**
     * 根据计数由多到少排列
     * zero-based
     * 
     * @param int $start            
     * @param int $end            
     * @param bool $withScores            
     */
    protected function rangeByIndex($start, $end, $withScores = true) {
        $key = $this->assembleKey ();
        $ret = $this->_redis->zRevRange ( $key, $start, $end, $withScores );
        $this->reset ();
        return $ret;
    }
    
    /**
     * 告诉计数器暂时不要清key
     * 
     * @return RedisModel
     */
    protected function hold() {
        $this->_holde = true;
        return $this;
    }
    
    /**
     * 与hold相反
     * 
     * @return RedisModel
     */
    protected function unhold() {
        $this->_holde = false;
        $this->reset ();
        return $this;
    }
    public function exists() {
        $key = $this->assembleKey ();
        $ret = $this->_redis->EXISTS ( $key );
        $this->reset ();
        return $ret;
    }
    public function set($value) {
        $key = $this->assembleKey ();
        $ret = $this->_redis->SET ( $key, $value );
        $this->reset ();
        return $ret;
    }
    
    /**
     * magic method for general command
     */
    public function __call($command, $params) {
        // hard code is redis existed
        if (! class_exists ( 'Redis' )) {
            return 0;
        }
        try {
            $key = $this->assembleKey ();
            array_unshift ( $params, $key );
            $ret = call_user_func_array ( array (
                    $this->_redis,
                    $command 
            ), $params );
            $this->reset ();
            return $ret;
        } catch ( \Exception $e ) {
            echo "RedisModel: __call $command";
        }
    }
    
    /**
     *
     * @return string $key
     */
    private function assembleKey() {
        static $lastKeyNum = 0;
        $key = $this->_prefix;
        $region = isset ( $this->segments ['region'] ) ? $this->segments ['region'] : 'national';
        
        $key .= $region;
        if (! isset ( $this->segments ['module'] )) {
            throw new \Exception ( 'module is not set' );
        }
        $count = 0;
        foreach ( $this->segments ['module'] as $module => $p1 ) {
            if ($region === "" && $count == 0)
                $key .= $module;
            else
                $key .= ':' . $module;
            if ($p1 !== null) {
                $key .= '-' . $p1;
            }
            $count += 1;
        }
        // recursive
        if (isset ( $this->segments ['logic'] )) {
            foreach ( $this->segments ['logic'] as $logic => $p2 ) {
                $key .= ':' . $logic;
                if ($p2 !== null) {
                    $key .= '-' . $p2;
                }
            }
        }
        
        return $key;
    }
    public function startTransaction() {
        return $this->_redis->multi ();
    }
    public function commitTransaction() {
        return $this->_redis->exec ();
    }
    public function discardTransaction() {
        return $this->_redis->discard ();
    }
}

?>