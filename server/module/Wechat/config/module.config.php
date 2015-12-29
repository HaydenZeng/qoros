<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
return array (
        
        'bjyauthorize' => array(
                'guards' => array(
                        'BjyAuthorize\Guard\Controller' => array(
                            ['controller' => ['wechat-index'], 'roles' => ['guest']],
                            ['controller' => ['wechat-user'],'roles' => ['guest']],
                        ),
                        'BjyAuthorize\Guard\Route' => array(
                            ['route' => 'wechat', 'roles' => ['guest']],
                            ['route' => 'wechat/index', 'roles' => ['guest']],
                            ['route' => 'wechat/user', 'roles' => ['guest']],
                            ['route' => 'wechat-at', 'roles' => ['guest']],
                            ['route' => 'wechat-menu', 'roles' => ['guest']],
                        )
                ),
        ),
        'router' => array (
                'routes' => array (
                        'wechat' => array (
                                'type' => 'Literal',
                                'options' => array (
                                        'route' => '/wechat',
                                        'defaults' => array (
                                                'controller' => 'wechat-index',
                                                'action' => 'index'
                                        )
                                ),
                                'may_terminate' => true,
                                'child_routes' => array(
                                    'index' => array (
                                        'type' => 'segment',
                                        'may_terminate' => true,
                                        'options' => array (
                                            'route' => '/index[/:action][/:id][/:cid]',
                                            'constraints' => array (
                                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                                'id' => '[0-9]+'
                                            ),
                                            'defaults' => array (
                                                'controller' => 'wechat-index',
                                                'action' => 'index'
                                            )
                                        )
                                    )
                                )
                        )
                 ),
        ),
        'service_manager' => array (
                
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
                'exception_template' => 'error/index',
                'template_map' => array (
                        'layout/wechat' => __DIR__ . '/../view/layout/layout_wechat.phtml',
                        'error/index' => __DIR__ . '/../view/error/index.phtml'
                ),
                'template_path_stack' => array (
                        __DIR__ . '/../view' 
                ),
        ),

        'module_layouts' => array(
                'Wechat' => array(
                    'default' => 'layout/wechat'
                )
        ),
        // Placeholder for console routes
        'console' => array(
        'router' => array(
            'routes' => array(
                'wechat-at' => array(
                    'options' => array(
                        'route' => 'wechat accessToken [--renew]',
                        'defaults' => array(
                            'controller' => 'wechat-index',
                            'action' => 'accessToken' 
                        ) 
                    ) 
                ),
                'wechat-menu' => array(
                    'options' => array(
                        'route' => 'wechat menu [--create] [--delete]',
                        'defaults' => array(
                            'controller' => 'wechat-index',
                            'action' => 'menu' 
                        ) 
                    ) 
                )
            ) 
        ) 
    ) ,
);
