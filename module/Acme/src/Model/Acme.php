<?php
namespace Acme\Model;
/**
 * Acme View Model | Acme/src/Acme/Controller/IndexController.php
 * @package Acme
 * @subpackage Acme View Model
 * @author Ron White <ronwhite562@gmail.com>
 * @version 1.0.0 (Dev-Master)
 * @since 2024-07-30
 */

class Acme
{
    /**
     * queryOrders
     * Returns order ids for client user (ie 83) whose 
     * delivered end date is null after n allowed days.
     * 
     * @method queryOrders
     * @access public
     * @param array $configs, all system configurations required for DB connection
     * @param array $params, key/value parameters for this query
     * @param int $cid, client user id (mock 83)
     * @param int $days, allowed n days from pickup before reporting missed.
     * @param int $iga, ignore this record after n days from shipment pick up date.
     * @return mixed $result, false if empty or [ 0=>[client_id=>int, client_name=>string, order_id=>int],..]
     */
    public function queryOrders( $configs, $params, $cid, $days, $iga )
    {
        $success = [];
        $foundResult = [];
        $status = 0;
        
        try 
        {    
            /* if lazy loading $conn doesn't already exist, 
             * instantiate a new one 
             */
            if ( @!is_object($conn) ) {
                $dbConn = [
                    'dns' => $configs[ $params['dbKey'] ]['dns'],
                    'username' => $configs[ $params['dbKey'] ]['username'],
                    'password' => $configs[ $params['dbKey'] ]['password']
                ];
                
                $dbService = new AcmeDb();   // Acme\AcmeDb
                $conn = $dbService->amDbConnection($dbConn);
            }
            
            /* 
             * MOCK DATA:
             * if using mock data, query csv file instead of db 
             */
            if( $configs['data_mocking'] === true ) {
                error_log('Acme IndexController queryOrder() using mock data.');

                $mockdata = $configs['ardmore_bin']['mockdata'];
                $cmd = "php -f ".escapeshellcmd( $mockdata );
                $cmd .= " ".escapeshellarg( $params['dbKey'] );
                $cmd .= " ".escapeshellarg( $cid );
                $cmd .= " ".escapeshellarg( $days );
                $cmd .= " 2>&1 | tee -a ../../tmp/rgo_mockdata.log";

                exec( $cmd, $foundResult, $status );
                
                if ( is_array($foundResult) && $status !== 1 ) {
                    return $foundResult;
                    
                } else {
                    throw new \Exception('queryRouteGuideOrders (mock) failed to return result');
                    
                }
            
            /* 
             * DATABASE CONNECTIONS:
             * active database available, query db as normal 
             */   
            } else if ( $configs['data_mocking'] === false ) {
                error_log('Acme IndexController queryOrders() using database conn.');
                
                /* @var $sql: uses same SQL shell scripting uses */
                include 'rgoRouteGuideOrdersSql.php';
                
                if ( is_string($sql) ) {
                    $stmt = $conn->prepare( trim($sql) );
                    $stmt->bindValue( ':cid', $cid, \PDO::PARAM_INT );
                    $stmt->bindValue( ':iga', $iga, \PDO::PARAM_INT );
                    $stmt->bindValue( ':days', $days, \PDO::PARAM_INT );
                    $stmt->execute();
                    while( $row = $stmt->fetch(\PDO::FETCH_ASSOC) ) {
                        $foundResult[] = $row;
                    }
                    if ( is_array( $foundResult ) ) return $foundResult;
                   
                } else {
                    throw new \Exception( __LINE__ . ':queryOrders sql wasnt available.');
                }
            }
        } catch( \Exception $e ) {  // add error log only.
            error_log( 'Error: Acme->queryOrders: ' . $e->getMessage() );
            return 1;
        }
    }
}