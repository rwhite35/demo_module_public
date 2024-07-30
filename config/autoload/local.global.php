<?php
/** 
 * use local.globals to override lower level configuration values 
 * of the same name and data type. this file loads last.
*/
define('MOD_EMAIL',"avrjoe@acme.com");
define('MOD_DOMAIN',"@acme.com");
define('DB_CLIENT_USER',getenv('USER'));
define('DB_CLIENT_PWD',"passpass");

return [
    // db credentials are final
    'db_1' => [
        'dns'       => "pgsql:dbname=acme;host=localhost",
        'username'  => "",
        'password'  => "",
    ],
    'db_2' => [
         'dns'       => "pgsql:dbname=demoapp;host=localhost",
         'username'  => DB_CLIENT_USER,
         'password'  => DB_CLIENT_PWD,
    ],
    'email_default' => MOD_EMAIL,
    'email_domain' => MOD_DOMAIN,

    // Acme Notification files are asynchronously executed 
    'acme_bin' => [
        'quickcheck'    => __DIR__ . '/../../module/acme/bin/quickcheck.php',
        'mockdata'      => __DIR__ . '/../../module/acme/bin/mockdata.php',
        'schedule'      => __DIR__ . '/../../module/acme/bin/cron.job',
        'sendmail'      => __DIR__ . '/../../module/acme/bin/sendmail.php',
    ],

    // received order component defaults
    'rgo_conf' => [
        'mod_name'          => "Received Orders",
        'prefix'            => "rgo_",
        'ignore_after_days' => 18,
        'maxallow_divisor'  => 2,
    ],
];