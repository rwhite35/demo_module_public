<?php
namespace Acme;
/**
 * Acme Module configurations, workers, routes and views.
 * these values overload values defined in local.global.php
 * when running in this modules context.
 */
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
            'AcmeDb' => AdapterAbstractServiceFactory::class,
        ]
    ],

    // register view controllers fro Acme UI
    'controllers' => [
        'aliases' => [
            'index' => Controller\IndexController::class,
        ],
        'factories' => [
            Controller\IndexController::class => InvokableFactory::class,
        ],
    ],

    // define Segment type route for Acme UI
    // allows placeholder tokens for route params action, id
    // constraints: `id` only matches 0-9, `action` matches alphanumerics.
    'router' => [
        'routes' => [
            'index' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/index[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'index',
                    ],
                ],
            ],
        ],
    ],

    'view_manager' => [
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