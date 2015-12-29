<?php
namespace Common\Redis;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RedisAbstractFactoryService implements AbstractFactoryInterface
{
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $parts = explode('\\', $requestedName);
        return count($parts) == 3 && $parts[1] == 'Redis';
    }
    
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $redisGateway = $serviceLocator->get('Common\Gateway\RedisGateway');
        $redisClass = "{$requestedName}Redis";
        return new $redisClass($redisGateway);
    }
}