<?php
namespace Common\Table;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Filter\Word\CamelCaseToUnderscore;
use Zend\Db\TableGateway\TableGateway;

class GatewayAbstractServiceFactory implements AbstractFactoryInterface
{
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $parts = explode('\\', $requestedName);
        return count($parts) == 3 && $parts[1] == 'TableGateway';
    }
    
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $parts = explode('\\', $requestedName);
        $dbAdapter = $serviceLocator->get('Zend\Db\Adapter\Adapter');
        $resultSetPrototype = new ResultSet ();
        $entityClass = "{$parts[0]}\\Entity\\{$parts[2]}Entity";
        $resultSetPrototype->setArrayObjectPrototype (new $entityClass());
        
        $filter = new CamelCaseToUnderscore();
        $tableNames = strtolower($filter->filter($parts[2])) . 's';
        return new TableGateway($tableNames, $dbAdapter, null, $resultSetPrototype);
    }
}