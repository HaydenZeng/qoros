<?php
return array (
    'bjyauthorize' => array(
        'guards' => array(
            'BjyAuthorize\Guard\Controller' => array(
                ['controller' => ['index'], 'roles' => ['guest']],
                ['controller' => ['user'], 'roles' => ['guest']],
                ['controller' => ['prize'], 'roles' => ['guest']]
            ),
            'BjyAuthorize\Guard\Route' => array(
                ['route' => 'home', 'roles' => ['guest']],
                ['route' => 'index', 'roles' => ['guest']],
                ['route' => 'user', 'roles' => ['guest']],
                ['route' => 'prize', 'roles' => ['guest']]
            ),
        ),
    ),
    'router' => array (
        'routes' => array (
            'home' => array (
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array (
                    'route' => '/',
                    'defaults' => array (
                        'controller' => 'index',
                        'action' => 'index'
                    )
                )
            ),
            'index' => array (
                'type' => 'segment',
                'options' => array (
                    'route' => '/index[/:action][/:id][/:page][/:size]',
                    'constraints' => array (
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                        'page' => '[0-9]+',
                        'size' => '[0-9]+'
                    ),
                    'defaults' => array (
                        'controller' => 'index',
                        'action' => 'index'
                    )
                )
            ),
            'user' => array (
                'type' => 'segment',
                'options' => array (
                    'route' => '/user[/:action][/:id][/:page][/:size]',
                    'constraints' => array (
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9_-]+',
                        'page' => '[0-9]+',
                        'size' => '[0-9]+'
                    ),
                    'defaults' => array (
                        'controller' => 'user',
                        'action' => 'index'
                    )
                )
            ),
            'prize' => array (
                'type' => 'segment',
                'options' => array (
                    'route' => '/prize[/:action][/:id][/:page][/:size]',
                    'constraints' => array (
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9_-]+',
                        'page' => '[0-9]+',
                        'size' => '[0-9]+'
                    ),
                    'defaults' => array (
                        'controller' => 'prize',
                        'action' => 'index'
                    )
                )
            )
        )
    ),

   'view_manager' => array (
        'template_map' => array (
            'layout/site' => __DIR__ . '/../view/layout/default.phtml'
        ),
        'template_path_stack' => array (
                'site' => __DIR__ . '/../view',
        ),
        'strategies' => array (
                'ViewJsonStrategy' 
        ),
        'exception_template' => 'error/index'
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
    'module_layouts' => array(
        'Site' => array(
            'default' => 'layout/site',
        )
    ),
    'doctrine' => array(
        'driver' => array(
            'Site_annotation_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'paths' => __DIR__ . '/../src/Site/Entity',
            ),
            'orm_default' => array(
                'drivers' => array(
                    'Site\Entity' => 'Site_annotation_driver',
                ),
            ),
        ),
    ),
);
