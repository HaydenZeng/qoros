<?php
namespace Site;

use Site\Controller\ArticleController;
use Site\Controller\GiftController;
use Site\Controller\GoodsController;
use Site\Controller\IndexController;
use Site\Controller\PrizeController;
use Site\Controller\UserController;
use Site\InputFilter\PersonRegister;
use Site\Model\Activity;
use Site\Model\Award;
use Site\Model\District;
use Site\Model\Messages;
use Site\Model\Share;
use Site\Model\User;
use Site\Model\Festival;
use Site\Model\GiftList;
use Site\Model\GiftPackage;
use Site\Model\Goods;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;
use Zend\Mvc\Application;
use Common\Exception\BusinessException;

class Module
{
    /**
     * @param \Zend\Mvc\MvcEvent $e The MvcEvent instance
     * @return void
     */
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $sm = $e->getApplication()->getServiceManager();
        $em = $eventManager->getSharedManager();
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'handleError'), 100);
        $eventManager->attach(MvcEvent::EVENT_RENDER_ERROR, array($this, 'handleError'), 100);
    }

    public function handleError(MvcEvent $e)
    {
        if ($e->getError() == Application::ERROR_ROUTER_NO_MATCH) {
            return;
        }
        if ($e->getError() == Application::ERROR_EXCEPTION) {
            $translator = $e->getApplication()->getServiceManager()->get('translator');
            $exception = $e->getParam('exception');
            if ($exception instanceof BusinessException) {
                // translate
                $status = intval($exception->getCode());
                $messsage = $translator->translate($exception->getMessage());
            } else {
                $status = 999;
                if (isset($_SERVER['APPLICATION_ENV']) && $_SERVER['APPLICATION_ENV'] == 'development') {
                    $messsage = $exception->getMessage();
                } else {
                    $messsage = '对不起, 系统出错了...';
                }
            }

            /* @var $request \Zend\Http\Request */
            $request = $e->getRequest();
            $response = $e->getResponse();
            if ($request->isXmlHttpRequest()) {
                // change status code from 500 to 200
                $response->setStatusCode(200);
                $response->getHeaders()->addHeaders(array(
                    'Content-type' => 'application/json; charset=utf8'
                ));
                $json = new JsonModel();
                $json->setVariables(array(
                    'status' => $status,
                    'message' => $messsage,
                    'content' => array()
                ));
                $json->setTerminal(false);
                $e->setViewModel($json);
                // mute other default exception handler
                $e->setError(false);
            }
        }
    }


    public function getControllerConfig()
    {
        return array(
            'factories' => [
                'Site\Controller\Index' => function ($sm) {
                    $userModel = $sm->getServiceLocator()->get('Site\Model\User');
                    $wechat = $sm->getServiceLocator()->get('Wechat\Model\Wechat');
                    $share = $sm->getServiceLocator()->get('Site\Model\share');
                    $activityModel = $sm->getServiceLocator()->get('Site\Model\Activity');
                    $district = $sm->getServiceLocator()->get('Site\Model\District');
                    $controller = new IndexController($wechat,$userModel,$share,$activityModel,$district);
                    return $controller;
                },
                'Site\Controller\User' => function ($sm) {
                    $userModel = $sm->getServiceLocator()->get('Site\Model\User');
                    $wechat = $sm->getServiceLocator()->get('Wechat\Model\Wechat');
                    $controller = new UserController($wechat,$userModel);
                    return $controller;
                },
                'Site\Controller\Prize' => function ($sm) {
                    $awardModel = $sm->getServiceLocator()->get('Site\Model\Award');
                    $userModel = $sm->getServiceLocator()->get('Site\Model\User');
                    $wechat = $sm->getServiceLocator()->get('Wechat\Model\Wechat');
                    $controller = new PrizeController($awardModel,$userModel,$wechat);
                    return $controller;
                },
            ],
            'aliases' => [
                'index' => 'Site\Controller\Index',
                'user' => 'Site\Controller\User',
                'prize' => 'Site\Controller\Prize',
            ],
        );
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Site\Model\User' => function ($sm) {
                    $model = new User();
                    return $model;
                },
                'Site\Model\District' => function ($sm) {
                    $model = new District();
                    return $model;
                },
                'Site\Model\Messages' => function ($sm) {
                    $model = new Messages();
                    return $model;
                },
                'Site\InputFilter\PersonRegister' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    return new PersonRegister($dbAdapter);
                },
                'Site\Model\Goods' => function ($sm) {
                    $model = new Goods();
                    return $model;
                },
                'Site\Model\Activity' => function ($sm) {
                    $model = new Activity();
                    return $model;
                },
                'Site\Model\Award' => function ($sm) {
                    $model = new Award();
                    return $model;
                },
                'Site\Model\Share' => function ($sm) {
                    $model = new Share();
                    return $model;
                },
            ),
            'aliases' => array(
                'Zend\Authentication\AuthenticationService' => 'Common\Authentication\Service',
            ),
        );
    }

    public function getViewHelperConfig() {
        return array(
            'factories' => array(
                'userHelper' => function($sm) {
                    return new \Site\Helper\UserHelper();
                }
            )
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

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}