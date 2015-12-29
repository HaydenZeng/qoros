<?php
return array(
        'bjyauthorize' => array(
            'default_role' => 'guest',
            'identity_provider' => 'Common\Authentication\BjyAuthorizeIdentityProvider',
            'role_providers' => array(
                    'BjyAuthorize\Provider\Role\Config' => array(
                        'guest' => array(
                            'children' => array(
                                'user' => array(
                                    'children' => array(
                                        'admin' => array()
                                    )
                                )
                            )
                        ),
                    ),
            ),

            'resource_providers' => array(
                    'BjyAuthorize\Provider\Resource\Config' => array(
                            'controller/admin' => array(),
                    ),
            ),

            'rule_providers' => array(
                    'BjyAuthorize\Provider\Rule\Config' => array(
                            'allow' => array(
                            ),
                            'deny' => array(
                            ),
                    ),
            ),
        ),
);