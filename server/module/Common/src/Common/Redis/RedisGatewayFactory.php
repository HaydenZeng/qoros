<?php
namespace Common\Redis;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Common\Redis\Gateway\RedisGateway;

class RedisGatewayFactory implements FactoryInterface {
    public function createService(ServiceLocatorInterface $serviceLocator) {
        $config = $serviceLocator->get('Config');
        return new RedisGateway($config['redis']);
    }
}
