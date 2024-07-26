<?php
namespace Acme;
/**
 * Acme\TableGatewayMapperFactory | Status Service
 * Adds TableGateway as an invokable service class (ServiceManager).
 * 
 * gets Acme\TableGateway from ServiceManager. Service address is  
 * defined in either the module config (ie Status/config/config.module.php)
 * or under the Acme config (vendor/acme/config/config.module.php).
 * Since this service is available to other resources, its defined in vendor/acme.
 * 
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014-2018 Zend Technologies USA Inc. (http://www.zend.com)
 */

use DomainException;

class TableGatewayMapperFactory
{
    public function __invoke($services)
    {
        if (! $services->has(TableGateway::class) ) {
            throw new DomainException(sprintf(
                'TableGatewayMapperFactory cannot create %s; missing %s. ' .
                  ' Check your configuration and namespace.',
                TableGatewayMapper::class,
                TableGateway::class
            ));
        } else {
            return new TableGatewayMapper($services->get(TableGateway::class));
            
        }
    }
}
