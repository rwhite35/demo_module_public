<?php
namespace Acme;
// use Laminas\Db\Adapter\AdapterAbstractServiceFactory;
// use Laminas\Db\Adapter\AdapterAbstractServiceFactoryTest;

/**
 * Module specific configurations in this context.
 * application configs(/../../config/local.global.php) overload
 * these values if also defined in local.global.php. 
 * 
 * `db_2` is configured for Postgres on localhost:<port>
 */
return [
    // db credentials are local scoped
    'db_1' => [
        'dns'       => "pgsql:dbname=acme;host=localhost",
        'username'  => "",
        'password'  => "",
    ],
    'db_2' => [
         'dns'       => "pgsql:dbname=demoapp;host=localhost",
         'username'  => "",
         'password'  => "",
    ],
    'email_default' => "",
    'email_domain' => "",
    'data_mocking' => true,
    'data_flat_files' => [
        'notifications' => "notifications",
        'orders' => "mobileusers",
        'schedule' => "",
    ],

    // any demo specific vars
    'acme' => [
        'mockToken' => "H4x0rVL38MxaWHDcKP0TFIruo_dUmMy",
        'mockUID' => "1337abc",
    ],

    // register service managers for this module
    'service_manager' => [
        'aliases' => [],
        'factories' => [
            \Acme\ArrayMapper::class     => \Acme\ArrayMapperFactory::class,
            \Acme\TableGateway::class     => \Acme\TableGatewayFactory::class,
            \Acme\TableGatewayMapper::class => \Acme\TableGatewayMapperFactory::class,
            
            // Acme Utilities database adapter
            'AcmeDb' => AdapterAbstractServiceFactory::class
        ]
    ],

    // register view controller for module
    'controllers' => [
        'aliases' => [
            // 'index' => Controller\IndexController::class
        ],
        'factories' => [
            // Controller\IndexController::class => Controller\IndexControllerFactory::class,
        ],
    ],

    // define API routes for modules
    'router' => [
        'routes' => [
            /*
            'notifications' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/notifications[/:action]',
                    'constraints' => [
                        'type' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'label' => '[a-zA-Z0-9_-]*'
                    ],
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => 1,
                'child_routes' => [
                    'create' => [
                        'type'    => Segment::class,
                        'options' => [
                            'route'    => '/create[/:action/]',
                            'constraints' => [
                                'type' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'label' => '[a-zA-Z0-9_-]*'
                            ],
                            'defaults' => [
                                'controller' => Controller\RouteGuideController::class,
                                'action'     => 'create',
                            ],
                        ],
                        'may_terminate' => 1,
                        'child_routes' =>[],
                    ],
                ],
            ],
            */

            // application dashboard from module
            /*
            'dashboard' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/dashboard[/:id]',
                    'defaults' => [
                        'controller' => Application\Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            */
        ],
    ],
];