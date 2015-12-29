<?php
namespace Common\Table;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TableAbstractServiceFactory implements AbstractFactoryInterface
{
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $parts = explode('\\', $requestedName);
        return count($parts) == 3 && $parts[1] == 'Table';
    }
    
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $parts = explode('\\', $requestedName);
        $module = $parts[0];
        $tableName = $parts[2];
        $tableGateway = $serviceLocator->get($module."\\TableGateway\\".$tableName);
        $tableClass = "{$requestedName}Table";
        $table = new $tableClass($tableGateway);
        return $table;
    }
}