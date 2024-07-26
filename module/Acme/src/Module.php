<?php
namespace Acme;
/**
 * Acme Utilties module
 * This file is required for Autoloader
 * 
 * Define any global functions for Acme\Utilities
 */
class Module
{
    /**
     * @method getConfig()
     * @return array this modules configurations
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
}