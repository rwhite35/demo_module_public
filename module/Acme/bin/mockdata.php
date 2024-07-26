<?php
/**
  * @var $mockCSV
  * flat file can be used in lue of a database setup.
  */
  $mockCSV = "/../tmp/acmeOrders.csv";

/**
 * convenience code for local development and demo purpose,
 * first block runs if/when called from command-line and passed argv[].
 * @param string argv[1] db_2 localhost db flag
 * @param int argv[2] db client id with at least read-only permissions
 * @param int argv[3] number of days(24 hours) before triggering the customer notification
 */
if ( !$argv[1] ) {
    echo "No params passed, nothing to search.\n";
    return false;
    
} else if ( $argv[1] === 'db_2' && $argv[2] ) {
    try {
        $clientId = $argv[2];
        $mockData = __DIR__ . $mockCSV;
        $fileSize = ( is_file($mockData) ) ? filesize( $mockData ) : 0;
        
        if( !$fileSize > 0 ) {
            throw new Exception( __LINE__ . ": Either mock data doesnt exist or wasnt able to open.\n");
            
        } else {
            $testDataArr = [];
            $foundResult = [];
            $fp = fopen( $mockData, 'rb' ); // read buffer
            
            while ( !feof($fp) ) {
                $row = fgetcsv($fp, $fileSize, ",");
                ($row !== false ) ? $testDataArr[] = $row : null;
            }
            fclose($fp);
            
            if( !is_array($testDataArr) && 
                    !array_key_exists(7, $testDataArr[0]) ) {
                throw new Exception( __LINE__ . "Mocking testData array failed to create.\n");
                
            } else {
                
                foreach ( $testDataArr as $array ) {
                    if ( $array[1] == $clientId && 
                        $array[4] == "(null)" || $array[4] == null 
                    ) {
                        // reduce testDataArr to only this clients orders
                        $foundResult[] = [
                            'client_name' => $array[5],
                            'order_id' => $array[0]
                        ];
                    
                    } else {
                        continue;
                        
                    }
                }   // close foreach loop
                
                
                // NOTE: exec is returning standard output. printf in one go.
                // exec will capture the output and sent to STDOUT
                // the printf is whats reported and not foundResult.
                if ( !empty($foundResult) ) {
                    for( $i=0; $i<count($foundResult); $i++ ) {
                        printf( "%s~%s;", $foundResult[$i]['client_name'], $foundResult[$i]['order_id'] );
                    }
                    
                } else {  // No found dispatch ids to found!
                    printf("No missed deliveries. Checked %s records for client %s\n", $c, $clientId);
                }
                
            }
            // echo "All done, Bye!\n";
        }
        
    } catch(Exception $e) {
        $error = $e->getMessage();
        error_log( __LINE__ . ": Errors: Acme\Utilities mockdata: " . $error );
        
    }
    
}


/**
 * called from schedulecheck script on manual crontab run,
 * depends on mockdata. runs all Users (only one in db),
 * returns customer users first order only when in demo mode.
 * 
 * @return array $result, first record in result.
 */
function scheduleCheckMockData( $mockingFlag, $clientId, $allowDays = null )
{
    try {
        
        // $clientId = $argv[2];
        $mockData = __DIR__ . $mockCSV;
        $fileSize = ( is_file($mockData) ) ? filesize( $mockData ) : 0;
        
        if( !$fileSize > 0 ) {
            throw new Exception("Either mock data doesnt exist or wasnt able to open.\n");
            
        } else {
            $testDataArr = [];
            $foundResult = [];
            $fp = fopen( $mockData, 'rb' ); // read buffer
            
            while ( !feof($fp) ) {
                $row = fgetcsv($fp, $fileSize, ",");
                ($row !== false ) ? $testDataArr[] = $row : null;
            }
            fclose($fp);
            
            if( !is_array($testDataArr) &&
                !array_key_exists(7, $testDataArr[0]) ) {
                    throw new Exception( __LINE__ . "Mocking testData array failed to create.\n");
                    
                } else {
                    
                    foreach ( $testDataArr as $array ) {
                        if ( $array[1] == $clientId &&
                            $array[4] == "(null)" || $array[4] == null
                            ) {
                                
                                $foundResult = [
                                    'client_name'       => $array[5],
                                    'order_id'          => $array[0],
                                    'rgo_pickup_date'   => $array[3],
                                ];
                                
                            } else {
                                continue;
                            }
                    }   // close foreach loop
                    
                    
                    // Returns a standard hash table with key/value pairs.
                    if ( !empty($foundResult) ) {
                        return $foundResult;
                        
                    } else {  // No found dispatch ids to found!
                        printf("No missed deliveries. Checked %s records for client %s\n", $c, $clientId);
                    }
                    
                }
                // echo "All done, Bye!\n";
        }
        
    } catch(Exception $e) {
        $error = $e->getMessage();
        error_log( __LINE__ . ": Errors: Acme\Utilities mockdata: " . $error );
        
    }
}


/**
 * helper method to format output when read from flat file.
 * passes mockdata by reference as read from flat file.
 *  proto [24]=>[ [0]=>H4x0r~123456789;H4x0r~987654321; ]
 * @method formatMockDataArray
 * @param array $foundResult mock data array.
 * @return array $array formatted mock data
 */
function formatMockDataArray( &$arrayRef )
{
    $temp = [];
    $cid = key( $arrayRef );
    $tt = explode( ";", $arrayRef[$cid][0] );   // could be 0, 1 or more
    unset($arrayRef);   // make sure we don't have a dereferenced object.
    
    array_pop($tt);
    $cnt = count($tt); // Array[ [0]=>H4x0r~123456789;H4x0r~987654321; ]
    if ($cnt > 0) {
        for( $j=0; $j<$cnt; $j++ ) {
            $tr = explode("~", $tt[$j]);
            $temp[$cid][] = [
                'client_name' => $tr[0],
                'order_id' => $tr[1]
            ];
        }
    }
    
    return $temp;
}
?>