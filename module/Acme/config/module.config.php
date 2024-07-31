<?php
namespace Acme;
/**
 * Acme Module configurations, workers, routes and views.
 * these values overload values defined in local.global.php
 * when running in this modules context.
 */
use Zend\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    // register service managers for this module
    'service_manager' => [
        'aliases' => [],
        'factories' => [
            \Acme\ArrayMapper::class     => \Acme\ArrayMapperFactory::class,
            \Acme\TableGateway::class     => \Acme\TableGatewayFactory::class,
            \Acme\TableGatewayMapper::class => \Acme\TableGatewayMapperFactory::class,
            
            // Acme Utilities database adapter
            // 'AcmeDb' => AdapterAbstractServiceFactory::class,
        ]
    ],

    // register view controllers fro Acme UI
    'controllers' => [
        'aliases' => [
            'index' => Controller\IndexController::class,
            // 'orders'  => Controller\Orders::class,
        ],
        'factories' => [
            Controller\IndexController::class => InvokableFactory::class,
            // Controller\OrdersController::class => InvokableFactory::class,
        ],
    ],

    // define Segment type route for Acme UI
    // allows placeholder tokens for route params action, id
    // constraints: `id` only matches 0-9, `action` matches alphanumerics.
    'router' => [
        'routes' => [
            'acme' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'index',
                    ],
                ],
            ],
        ],
    ],

    'view_manager' => [
        'doctype'                  => 'HTML5',
        'display_not_found_reason' => false,
        'display_exceptions'       => false,

        // Applications layout.phtml is common to all modules
        'template_map' => [
            'layout/layout'         => __DIR__ . '/../../Application/view/layout/layout.phtml',
            // 'index/index'           => __DIR__ . '/../view/index/index.phtml',
        ],
        // Acme module specific view resources
        'template_path_stack' => [
            'acme' => __DIR__ . '/../view',
        ],
    ],

    // project specific configurations
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
];