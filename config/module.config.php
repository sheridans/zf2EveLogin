<?php
namespace zf2EveLogin;
return array(
   'router' => array(
       'routes' => array(
           'zf2-eve-login' => array(
               'type'    => 'Literal',
               'options' => array(
                   'route'    => '/eve-sso',
                   'defaults' => array(
                       '__NAMESPACE__' => 'zf2EveLogin\Controller',
                       'controller'    => 'Index',
                       'action'        => 'index',
                   ),
               ),
               'may_terminate' => true,
               'child_routes' => array(
                   'login' => array(
                       'type' => 'Literal',
                       'options' => array(
                           'route' => '/login',
                           'defaults' => array(
                               'controller' => 'zf2EveLogin\Controller\Index',
                               'action' => 'login',
                           ),
                       ),
                   ),
                   'authorise' => array(
                       'type' => 'Literal',
                       'options' => array(
                           'route' => '/authorise',
                           'defaults' => array(
                               'controller' => 'zf2EveLogin\Controller\Index',
                               'action' => 'authorise',
                           ),
                       ),
                   ),
                   'logout' => array(
                       'type' => 'Literal',
                       'options' => array(
                           'route' => '/logout',
                           'defaults' => array(
                               'controller' => 'zf2EveLogin\Controller\Index',
                               'action' => 'logout',
                           ),
                       ),
                   ),
               ),
           ),
       ),
   ),
    'controllers' => array(
        'invokables' => array(
            'zf2EveLogin\Controller\Index' => 'zf2EveLogin\Controller\IndexController',
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'service_manager' => array(
        'invokables' => array(
            'eve_sso_auth_service' => 'zf2EveLogin\Service\authService',
        ),
    ),
    'zf2EveLogin' => array(
        'server_url'    => 'https://login.eveonline.com',
        'user-agent'    => 'zf2EveLogin by Lost Packet'
    ),
);