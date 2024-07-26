<?php
namespace Acme\Utilities;
/**
 * Acme\Utilities | vendor/acme/utilities/src/AcmeDb.php
 * @package Acme\Utilities
 * @subpackage AcmeDb 
 * @author ron white, ronwhite562@gmail.com
 * @version 1.0
 * @since 2018-04-19
 */


 /** 
  * AcmeDb
  * This package was created to use a custom ODBC dsn as required.  
  * The ZF3 Db/Adapter may not handle all the Acme Logistics data store requirements.
  *
  * Additional methods will be added as different data sources are
  * added to the system. Uses PHP's standard PDO implmentation.
  */
class AcmeDb
{

	/**
	 * amDbConnection
	 * Returns a database connection reference.
	 * Calling service is responsible for setting attributes,
	 * generating queries, and handling returned result.
	 * @param string $dsn, expects driver:vendor specific dsn.
	 * prototype 'odbc:postres' or 'sqlsrv:Server=foo-sql,1433;Database=db_n'
	 *
	 * @return object reference on success.
	 */
	public function amDbConnection( $configs )
	{   
	    $mes = "AcmeDb::amDbConnection working...\n";
	    $log = __DIR__."/../tmp/debug.log";
	    
	    foreach($configs as $key => $value) {
	        switch($key) {
	            case "dsn" : $dsn = $value;
	            case "username" : $username = $value;
	            case "password" : $password = $value;
	            break;
	        }
	    }
	   
		$driver = explode( ":", strtolower($dsn) );
		$mes .= __LINE__ . ": Acme dsn driver: " . $driver[0] . "\n";
        
		try 
		{
			switch( $driver[0] ) {
				
				// PDO_ODBC type connections
				case "odbc":
					$mes .= ", tried PDO_ODBC instanced \n";
					$sm = new \PDO(
						$dsn,
						$username,
						$password
					);
				break;

				// PDO_PGSQL type connections
				case "pgsql,postgres,postgresql":
				    $mes .= ", tried PDO_PGSQL instanced \n";
				    $sm = new \PDO(
				        $dsn,
				        $username,
				        $password
				    );
				break;
				
				// PDO_SQL type connections
				case "sqlsrv":
				    $mes .= ", tried PDO_SQLSRV instanced \n";
				    $sm = new \PDO(
				        $dsn,
				        $username,
				        $password
				        );
				    break;
			}
			
			if( is_object($sm) ) { // enable/disable additional properties
			    $sm->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
			    
			} else {
			    $sm = false;
			    $e = __LINE__.": failed to create new PDO service using " . $driver[0] ."\n";
			    throw new \Exception($e);   
			}
		} catch( \Exception $e ) {
			$mes .= "Errors: " . $e->getMessage() . "\n";
			error_log($mes,3,$log);
		}
		return $sm;	
	}
}
