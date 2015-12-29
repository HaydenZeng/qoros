<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Wechat;

use Wechat\Controller\IndexController;
use Wechat\Controller\PayController;
use Wechat\Controller\UserController;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Controller\ControllerManager;
use Zend\Di\ServiceLocator;
use Zend\ServiceManager\ServiceManager;

class Module {
    public function onBootstrap(MvcEvent $e) {
        $eventManager = $e->getApplication ()->getEventManager ();
        $moduleRouteListener = new ModuleRouteListener ();
        $moduleRouteListener->attach ( $eventManager );
    }
    
    public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function getControllerConfig()
    {
        return array(
            'factories' => [
                'Wechat\Controller\Index' => function ($sm) {
                    $wechat = $sm->getServiceLocator()->get('Wechat\Model\Wechat');
                    $userModel = $sm->getServiceLocator()->get('Site\Model\User');
                    $controller = new IndexController($wechat,$userModel);
                    return $controller;
                }
            ],
            'aliases' =>  [
                'wechat-index' => 'Wechat\Controller\Index'
            ],
        );
    }
    
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Wechat\Model\Wechat' => function(ServiceManager $sm) {
                    $config = $sm->get('config');
                    $client = new \Zend\Http\Client();
                    $client->setAdapter(new \Zend\Http\Client\Adapter\Curl());
//                    $redis = $sm->get('Wechat\Redis\Wechat');
                    return new \Wechat\Model\Wechat($config['wechat'], $client);
                }
            ) 
        );
    }
    
    public function getAutoloaderConfig() {
        return array (
                'Zend\Loader\ClassMapAutoloader' => array (
                        __DIR__ . '/autoload_classmap.php' 
                ),
                'Zend\Loader\StandardAutoloader' => array (
                        'namespaces' => array (
                                __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__ 
                        ) 
                ) 
        );
    }
}
