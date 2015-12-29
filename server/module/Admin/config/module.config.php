<?php
return array (
    'bjyauthorize' => array(
        'guards' => array(
            'BjyAuthorize\Guard\Controller' => array(
                ['controller' => ['admin-index'], 'roles' => ['admin']],
                ['controller' => ['admin-user'], 'action' => ['login','importUser'],'roles' => ['guest']],
                ['controller' => ['admin-user'], 'action' => ['index','list'],'roles' => ['admin']],
                ['controller' => ['admin-goods'], 'roles' => ['admin']],
                ['controller' => ['admin-award'], 'roles' => ['admin']]
            ),
            'BjyAuthorize\Guard\Route' => array(
                ['route' => 'admin', 'roles' => ['guest','admin']]
            ),
        ),
    ),
    'router' => array (
        'routes' => array (
            'admin' => array (
                'type' => 'Segment',
                'options' => array (
                     'route' => '/adm[/][:controller][/:action][/:id][/:page][/:size]',
                     'constraints' => array (
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                     ),
                    'defaults' => array (
                        'controller' => 'admin-index',
                        'action' => 'index'
                    )
                ),
              )
         ),
    ),
   'view_manager' => array (
            'display_not_found_reason' => true,
            'display_exceptions' => true,
            'doctype' => 'HTML5',
            'not_found_template' => 'error/404',
            'exception_template' => 'error/index',
            'template_map' => array (
                    'layout/admin' => __DIR__ . '/../view/layout/layout_admin.phtml',
                    'layout/login' => __DIR__ . '/../view/layout/login.phtml'
            ),
            'template_path_stack' => array (
                    __DIR__ . '/../view' 
            ),
    ),

    'module_layouts' => array(
        'Admin' => array(
            'default' => 'layout/admin',
        )
    ),

    // Placeholder for console routes
    'console' => array (
        'router' => array (
            'routes' => array ()
        ) 
    ),
);
