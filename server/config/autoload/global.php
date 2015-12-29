<?php
    use Zend\Navigation\Page\AbstractPage;
    /**
     * Global Configuration Override
     *
     * You can use this file for overriding configuration values from modules, etc.
     * You would place values in here that are agnostic to the environment and not
     * sensitive to security.
     *
     * @NOTE: In practice, this file will typically be INCLUDED in your source
     * control, so do not include passwords or other sensitive information in this
     * file.
     */

    return array(
        'logger' => array(
            'path' => '/data/log/error.log',
            'priority' => \Zend\Log\Logger::INFO,
        ),
        'navigation' => array(

            'default' => array(
                array(
                    'label' => '用户管理',
                    'route' => 'admin/admin-user/userList',
                    'class'=>'nav-top-item',
                    'pages' => array (
                        array('label' => '用户列表', 'route' => 'admin/admin-user/userList'),
                        array('label' => '新建用户', 'route' => 'admin/admin-user/create')
                    )
                ),


                array('label' => '财会大学', 'route' => 'admin', 'controller' => 'admin-article', 'action' => 'articleCategories', 'class' => 'list-2'),
                array('label' => '反馈', 'route' => 'admin', 'controller' => 'admin-feedback', 'action' => 'list','class' => 'list-2'),
            ),
        ),
        'service_manager' => array(
            'factories' => array(
                'navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',
                'default' => 'Zend\Navigation\Service\DefaultNavigationFactory',
            ),
        ),
        //必须卸载全局里，用来覆盖插件里的403page bzhang
        'view_manager' => array (
            'template_map' => array (
                'error/403' => __DIR__ . '/../../module/Common/view/error/403.phtml',
                'error/404' => __DIR__ . '/../../module/Common/view/error/404.phtml',
            ),
        ),
    );
