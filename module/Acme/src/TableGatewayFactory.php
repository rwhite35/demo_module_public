<?php
namespace Acme;
/**
 * Instantiates TableGateway as an instance of Laminas\Db\Adapter\Adapter
 * The min. fields for Adapter are driver, database, username and password.
 * 
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

use DomainException;
use Laminas\ApiTools\Configuration\ConfigResource;

class TableGatewayFactory
{
    public function __invoke($services)
    {
        
        $db = 'AcmeDb';                     // Adapter name
        $table = 'oauth_access_tokens';     // will be overwritten when called.
        
        if ($services->has('config')) $config = $services->get('config');
        
        if (! $services->has($db)) {
            throw new DomainException(sprintf(
                'TableGatewayFactory unable to create %s with adapter %s name',
                TableGateway::class,
                $db
            ));
        }

        return new TableGateway($table, $services->get($db));
    }
}
