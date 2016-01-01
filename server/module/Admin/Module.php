<?php
namespace Admin;

use Admin\Controller\AwardController;
use Admin\Controller\GiftController;
use Admin\Controller\GoodsController;
use Admin\Controller\MessageController;
use Admin\Controller\UserController;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;
use Admin\Controller\IndexController;

class Module
{
    public function onBootstrap(MvcEvent $e) {
        $eventManager = $e->getApplication ()->getEventManager ();
        $moduleRouteListener = new ModuleRouteListener ();
        $moduleRouteListener->attach ( $eventManager );
    }

    public function getControllerConfig()
    {
        return array (
            'factories' => [
                'Admin\Controller\Index' => function ($sm) {
                    $wechat = $sm->getServiceLocator()->get('Wechat\Model\Wechat');
                    $userModel = $sm->getServiceLocator()->get('Site\Model\User');
                    $share = $sm->getServiceLocator()->get('Site\Model\share');
                    $activityModel = $sm->getServiceLocator()->get('Site\Model\Activity');
                    $controller = new IndexController($wechat,$userModel,$share,$activityModel);
                    return $controller;
                },
                'Admin\Controller\User' => function ($sm) {
                    $wechat = $sm->getServiceLocator()->get('Wechat\Model\Wechat');
                    $userModel = $sm->getServiceLocator()->get('Site\Model\User');
                    $districtModel = $sm->getServiceLocator()->get('Site\Model\District');
                    $controller = new UserController($wechat,$userModel,$districtModel);
                    return $controller;
                },
                'Admin\Controller\Goods' => function ($sm) {
                    $goodsModel = $sm->getServiceLocator()->get('Site\Model\Goods');
                    $controller = new GoodsController($goodsModel);
                    return $controller;
                },
                'Admin\Controller\Award' => function ($sm) {
                    $awardModel = $sm->getServiceLocator()->get('Site\Model\Award');
                    $activityModel = $sm->getServiceLocator()->get('Site\Model\Activity');
                    $controller = new AwardController($awardModel,$activityModel);
                    return $controller;
                }
            ],
            'aliases' => [
                'admin-index' => 'Admin\Controller\Index',
                'admin-user' => 'Admin\Controller\User',
                'admin-goods' => 'Admin\Controller\Goods',
                'admin-award' => 'Admin\Controller\Award'
            ]
        );
    }
    
    public function getServiceConfig()
    {
         return array(
            'factories' => array (
            ),
        );
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    
    public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }
}