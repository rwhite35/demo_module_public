<?php
namespace Application;

use Laminas\Mvc\MvcEvent;
use Laminas\Mvc\ModuleRouteListener;

class Module
{
    const VERSION = '1.0.0-dev';

    public function onBootstrap(MvcEvent $e)
    {
        // instantiate Mvc resources
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        printf("App.onBootstrap() has moduleRouteListener with attached event ");
        print(var_dump($moduleRouteListener));
    }

    public function getConfig()
    {
        $modconfigs = __DIR__ . '/config/module.config.php';
        if(file_exists($modconfigs)) {
            return include $modconfigs;
        } else {
            error_log(TAG . " getConfig critical stop error: no config found.");
            exit();
        }
    }
}