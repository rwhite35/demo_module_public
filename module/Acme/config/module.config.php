<?php
namespace Acme;
use Zend\Db\Adapter\AdapterAbstractServiceFactory;
// use Zend\Db\Adapter\AdapterAbstractServiceFactoryTest;

/**
 * Demo Module configurations defined in global scope.  
 * Configs are specific to each the modules classes and resources.
 * @Note: `db_2` is configured for Postgres on localhost:<port>
 * @Note: getenv('USER') should return $_ENV['USER'] which,
 *        should also be the read-only DB User name.
 */
define('MOD_EMAIL',"avrjoe@acme.com");
define('MOD_DOMAIN',"@acme.com");
define('DB_USER',getenv('USER'));
define('DB_PWD',"fakePwd");

return [
    'db_1' => [
        'dns'       => 'pgsql:dbname=acme;host=localhost',
        'username'  => '<prod_db_user>',
        'password'  => '<prod_db_pwd>'
    ],
    'db_2' => [
         'dns'       => 'pgsql:dbname=demoapp;host=localhost',
         'username'  => DB_USER,
         'password'  => DB_PWD
    ],
    "email_default" => MOD_EMAIL,
    "email_domain" => MOD_DOMAIN,
    "data_mocking" => true,
    "data_flat_files" => [
        "notifications" => "",
        "orders" => "mobileusers",
        "schedule" => "",
    ],
    // any demo specific vars
    'acme' => [
        "mockToken" => "H4x0rVL38MxaWHDcKP0TFIruo_dUmMy",
        "mockUID" => "1337abc",
    ],
    'service_manager' => [
        'alias' => [],
        'factories' => [
            \Acme\ArrayMapper::class     => \Acme\ArrayMapperFactory::class,
            \Acme\TableGateway::class     => \Acme\TableGatewayFactory::class,
            \Acme\TableGatewayMapper::class => \Acme\TableGatewayMapperFactory::class,
            
            // Acme Utilities database adapter
            'AcmeDb' => AdapterAbstractServiceFactory::class
        ]
    ]
];