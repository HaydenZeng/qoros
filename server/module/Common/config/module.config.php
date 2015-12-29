<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
return array (
        'router' => array (
        ),
        'service_manager' => array (
                'abstract_factories' => array (
                        'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
                        'Zend\Log\LoggerAbstractServiceFactory',
                        'Common\Table\TableAbstractServiceFactory',
                        'Common\Table\GatewayAbstractServiceFactory',
                        'Common\Redis\RedisAbstractFactoryService'
                ),
                'aliases' => array (
                        'translator' => 'MvcTranslator' 
                ),
        ),
        'translator' => array (
                'locale' => 'zh_CN',
                'translation_file_patterns' => array (
                        array (
                                'type' => 'gettext',
                                'base_dir' => __DIR__ . '/../language',
                                'pattern' => '%s.mo' 
                        ) 
                ) 
        ),
        'controllers' => array (
                'invokables' => array (
                    
                ) 
        ),
        'view_manager' => array (
                'display_not_found_reason' => true,
                'display_exceptions' => true,
                'doctype' => 'HTML5',
                'not_found_template' => 'error/404',
                'exception_template' => 'error/index',
                'template_map' => array (
                        'layout/common' => __DIR__ . '/../view/layout/layout.phtml',
                        'error/index' => __DIR__ . '/../view/error/index.phtml',
//                        'error/404' => __DIR__ . '/../view/error/404.phtml'
                ),
                'template_path_stack' => array (
                        __DIR__ . '/../view' 
                )
        ),
        // Placeholder for console routes
        'console' => array (
                'router' => array (
                        'routes' => array () 
                ) 
        ),
        'session' => array(
            'use_cookies' => true,
//            'cookie_domain'=>'.'.$_SERVER['SERVER_NAME'],
            'cookie_life_time'=>86400,
        ),

        'module_layouts' => array(
            'Common' => array(
                'default' => 'layout/common',
            )
        ),
);
