<?php
namespace Acme;
/**
 * Acme module
 * This file is `main` and loaded by Autoloader 
 * using path ./module/Acme/src/Module.php.
 * - loads configuration and required dependencies. 
 */
use Laminas\ModuleManager\Feature\ConfigProviderInterface;

class Module implements ConfigProviderInterface
{
    /**
     * @method getConfig()
     * @return array this modules configuration file
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
    
}