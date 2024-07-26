<?php
namespace Acme;
/**
 * Acme\TableGateway | Acme Utilities
 * Provides a constructor for creating a database adapter 
 * using the specified DB info.
 * 
 * The Table Gateway subcomponent provides an object-oriented representation 
 * of a database table; its methods mirror the most common table operations. 
 * In code, the interface resembles:
 * 
 * @see https://zendframework.github.io/zend-db/table-gateway/
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014-2018 Zend Technologies USA Inc. (http://www.zend.com)
 */

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\TableGateway\TableGateway as ZFTableGateway;
use Zend\Hydrator\ObjectProperty as ObjectPropertyHydrator;

class TableGateway extends ZFTableGateway
{
    public function __construct(
        $table,                     // table to query
        AdapterInterface $adapter,  // the DB connection ( config[db][adapters] )
        $features = null            // for TableGatewayFeaturesDecorator is defined  
        
    ) {
        
        ob_start();
        echo "TableGateway Constructor adaper: ";
        print_r($adapter);
        $str = ob_get_clean();
        error_log($str, 3, __DIR__."/log/debug.log");
      
        //$resultSet = new HydratingResultSet(new ObjectPropertyHydrator(), new Entity());
        return parent::__construct(
            $table, 
            $adapter, 
            $features, 
            
            // should be switched to Acme ArrayMapper
            $resultSet = new HydratingResultSet(
                new ObjectPropertyHydrator() 
                // new Entity()
            ));
    }
}
