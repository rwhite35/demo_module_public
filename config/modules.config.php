<?php
/**
 * Application Dependencies | config/modules.config.php
 * list of frameworks, modules and widgets for application
 * 
 * Loads first, followed by each listed modules config 
 * file at: ./module/{module_name}/config/module.config.php
 */
return [
    'Laminas\Session',
    'Laminas\Hydrator',
    'Acme',
];