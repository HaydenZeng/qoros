<?php
namespace Wechat\Util;

use Doctrine\Common\Cache\ZendDataCache;
use Zend\Log\Logger;
use Wechat\Redis\WechatRedis;
use Zend\Session\Container;
use Zend\Session\SaveHandler\Cache;
use Zend\Session\SessionManager;

class WechatUtil extends \Wechat
{
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var Container
     */
    private $cache;
    
    public function __construct(Logger $logger, array $options)
    {
        $this->logger = $logger;
        $this->cache = new ZendDataCache();
        parent::__construct($options);
    }
    
    protected function log($log)
    {
        if (is_array($log)) $log = print_r($log,true);
        $this->logger->info($log);
    }
    
    protected function setCache($cachename, $value, $expired = 0)
    {
//        return $this->cache->save($cachename, $value, $expired);
//        return $this->cache->setCache($cachename, $value, $expired);
    }
    
    protected function getCache($cachename)
    {
//        return $this->cache->fetch($cachename);
//        return $this->cache->getCache($cachename);
    }
    
    protected function removeCache($cachename)
    {
//        return $this->cache->deleteAll();
//        return $this->removeCache($cachename);
    }
}