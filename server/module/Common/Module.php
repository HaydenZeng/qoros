<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Common;

use Common\HttpApi\EmailApi;
use Zend\Form\Element\Email;
use BjyAuthorize\View\RedirectionStrategy;
use Zend\Http\Client\Adapter\Curl;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Common\SocialApi\SinaWeiboApi;
use Common\SocialApi\QQConnectApi;
use Common\SocialApi\WechatApi;
use Zend\Session\Config\SessionConfig;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Zend\Authentication\AuthenticationService;

class Module {
    
    public function onBootstrap(MvcEvent $e) {
        $eventManager = $e->getApplication ()->getEventManager ();
        $sm         = $e->getApplication()->getServiceManager();

        $moduleRouteListener = new ModuleRouteListener ();
        $moduleRouteListener->attach ( $eventManager );

        $this->customLayout($e);

        //403 跳转 bzhang
        $redirectUri = $e->getRequest()->getRequestURI();
        $redirectUri = urlencode($redirectUri);
        $strategy = new RedirectionStrategy();
//        $strategy->setRedirectRoute('user',array('action'=>'unauthorized'),array('query'=>array('redirect'=>$redirectUri)));
        $strategy->setRedirectUri('/qoros/user/unauthorized?redirect='.$redirectUri);
        $eventManager->attach($strategy);


    }

    public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array (
                'Common\HttpApi\SmsApi' => function ($sm) {
                    $curlAdapter = new Curl();
                    return new \Common\HttpApi\SmsApi($curlAdapter);
                },
                'Zend\Log' => function ($sm) {
                    $config = $sm->get('Config');

                    $log = new \Zend\Log\Logger();
                    $writer = new \Zend\Log\Writer\Stream('./'.$config['logger']['path']);
                    $log->addWriter($writer);

                    $filter = new \Zend\Log\Filter\Priority($config['logger']['priority']);
                    $writer->addFilter($filter);

                    return $log;
                },
                'Common\Authentication\Adapter' => function ($sm) {
                    $em = $sm->get('doctrine.entitymanager.orm_default');
                    $adapter = new \Common\Authentication\DoctrineAdapter($em);
                    return $adapter;
                },
                'Common\Authentication\Service' => function ($sm) {
                    $service = new AuthenticationService();
                    $authAdapter = $sm->get('Common\Authentication\Adapter');
                    $service->setAdapter($authAdapter);
                    return $service;
                },
                'Common\Authentication\BjyAuthorizeIdentityProvider' => function ($sm) {
                    $authService = $sm->get('Common\Authentication\Service');
                    return new \Common\Authentication\BjyAuthorizeIdentityProvider($authService);
                }
            )
        );
    }

    public function getControllerPluginConfig()
    {
        return array(
            'factories' => array(
                'authentication' => function ($pm) {
                    $serviceLocator = $pm->getServiceLocator();
                    $authAdapter = $serviceLocator->get('Common\Authentication\Adapter');
                    $authService = $serviceLocator->get('Common\Authentication\Service');
                    $controllerPlugin = new \Common\Controller\Plugin\Authentication();
                    $controllerPlugin->setAuthService($authService);
                    $controllerPlugin->setAuthAdapter($authAdapter);
                    return $controllerPlugin;
                },
            ),
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
    
    public function customLayout(MvcEvent $e)
    {
        //diffent layout of modules @bzhang
        $e->getApplication()->getEventManager()->getSharedManager()->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', function($e) {
            $controller      = $e->getTarget();
            $controllerClass = get_class($controller);
            $moduleNamespace = substr($controllerClass, 0, strpos($controllerClass, '\\'));
            $config          = $e->getApplication()->getServiceManager()->get('config');
        
            $routeMatch = $e->getRouteMatch();
            $actionName = strtolower($routeMatch->getParam('action', 'not-found')); // get the action name
        
            if (isset($config['module_layouts'][$moduleNamespace][$actionName])) {
                $controller->layout($config['module_layouts'][$moduleNamespace][$actionName]);
            }elseif(isset($config['module_layouts'][$moduleNamespace]['default'])) {
                $controller->layout($config['module_layouts'][$moduleNamespace]['default']);
            }
        }, 100);
    }
}
