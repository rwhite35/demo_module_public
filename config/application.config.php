<?php
/**
 * put application specific configurations here
*/
return [
    'modules' => require __DIR__ . '/modules.config.php', // list of modules
    'module_listener_options' => [
        'module_paths' => [
            './module',
            '.vendor',
        ],

        'config_glob_paths' => [
            realpath(__DIR__) . '/autoload/{{,*.}global,{,*.}local}.php',
        ],

        // set config cache true before deploy to production
        'config_cache_enabled' => false,

        // key used to create the configuration cache file name
        'config_cache_key' => 'application.config.cache',

        // enable a module class map cache
        'module_map_cache_enabled' => false,

        // key used to create the class map cache file name
        'module_map_cache_key' => 'application.module.cache',

        // path to merged configuration.
        'cache_dir' => 'data/cache/',
    ],

    // Used to create an own service manager. May contain one or more child arrays.
    // 'service_listener_options' => [
    //     [
    //         'service_manager' => $stringServiceManagerName,
    //         'config_key'      => $stringConfigKey,
    //         'interface'       => $stringOptionalInterface,
    //         'method'          => $stringRequiredMethodName,
    //     ],
    // ],

    // Initial configuration with which to seed the ServiceManager.
    // Should be compatible with Zend\ServiceManager\Config.
    // 'service_manager' => [],

];
