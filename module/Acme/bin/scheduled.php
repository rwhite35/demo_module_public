<?php
/**
 * Scheduled Notification | Acme/bin/scheduled.php
 * @package Acme Script
 * @subpackage Notifications
 * 
 * Scheduled Notifications worker
 * procedural script executed from a scheduled crontab event.
 * 
 * IMPORTANT: number of users and their associated rules count is heurtic.
 * Those properties are figured out programatically at runtime.
 * 
 * Cronjobs run hourly(1), daily(2), weekly(3), monthly(4)
 * To list the cron jobs, use cmd  crontab -l, to edit   crontab -e
 * 
 * Users and Notification Rules
 * Notification rules are a one(user) to many(rules) relationship. 
 * a users notification rules are passed one at a time, but do not 
 * report to user until the process handles all rules in the queue.
 *  
 * Rule key/values define each notification condition for a given order.
 * for example, the number of days an order has before a pick-up
 * reminder is triggered (not included in demo project).
 * 
 * @var array $foundResult a multidim array of each rule result (if any, null is valid).
 * A db result can be empty or an array of fields as defined in the response model.  
 * additional key/values are pushed on to the result stack including [report] and [email]. 
 */

/* 
 * crontab passed arguments 
 */
$service = $argv[1];                        // service name as file source for notifications rule
$ns = ucfirst($service);                    // namespace Notifications
$interval = ( $argv[2] ) ? $argv[2] : 2;    // int: intervals, default 2 (daily)

/*
 * loads model and module resources in async cronjob context
 */
include __DIR__ . "/../src/RulesAbstractFactory.php";
include __DIR__ . "/../src/JsonFactory.php";
include __DIR__ . "/../src/AcmeDb.php";
include __DIR__ . "/sendmail.php";

use Acme\Utilities\JsonFactory;
use Acme\Utilities\AcmeDb;

/* 
 * local vars should have scope through out the script.
 */
$errors ='';
$configs = include __DIR__ . "/../config/module_config.php";
$filePath = __DIR__ ."/../tmp/" . $service . ".json";
$jsonArr = [];      // rules stored in the JSON file tmp/notifications.json
                    // proto: Arr[ 0=>[ user_id=>[ client_id=>int, plus_days=>int, check_status=>int,..],..],..]
$usersArr = [];     // Only these users who have a rule that matched this cronjobs interval
                    // proto: Arr[ 0=>string, 1=>string,..]  the user ids ie 0=>'rgo_1b10db...',
$params = [];       // ONLY the rules to be checked(check_status) on this cronjob interval
                    // proto: Arr[ 0=>[ user_id=>[ client_id=>string, plus_days=>int, check_status=>int,..],..],..]
$foundResult = [];  // see header docblock


/* Step 1: load all service rules into mememory.
 * The JSON file should be named <service_name>.json and located
 * in the applications data/ dir.
 */
if( is_file( $filePath ) ) {
    
    $jsonObj = new JsonFactory();
    $jsonArr = $jsonObj->loadJsonFromFile( $filePath, "r" ); 
    $jcnt = count( $jsonArr );
  
    
    /* Step 2: Process only the rules that match this interval.
     * Parse jsonArr, assign rules that match this intervals(check_status) 
     * and are not disabled. 
     */
    foreach( $jsonArr as $array ) {
        $rko = key($array); // user id key ie. rgo_1b10...
        if( (int) $array[$rko]['check_status'] != $interval || 
            $array[$rko]['active'] === "disabled" 
        ) { 
            continue; // to next rule
            
        } else {
            $usersArr[] = $rko;         // users to check
            $params[] = $array;         // the rule to process.
            
        }    
    }       // close foreach
    
    /* Step 3 Sort unordered arrays 
     * both userArr array and params array are ordered by user id.
     */
    array_multisort($usersArr, SORT_ASC, SORT_STRING, $params );
    
} else {
    error_log( __LINE__ . ": scheduled was'\nt able to open JSON file for reading, exiting here." );
    exit();
    
}

/* Check each users rules, and handle the data results set.
 * This block processes both database results and mock data results.
 */ 
if ( $usersArr && $params ) {
    error_log ( "scheduled cron job interval $interval is working..." );
    
    $dbKey = ( $configs['data_mocking'] === false ) ? "db_1" : "db_2";
    $dbObj = new AcmeDb();
    $conn = $dbObj->amDbConnection( $configs[$dbKey] );
    $ukey = '';
    $reportString = '';
    $emailReport = '';
    $rcnt = count($params);
    $uniqueUsers = array_count_values($usersArr);
    $uniqueUkeys = array_keys($uniqueUsers); 
    
    /*
     * Step 4.1 WITH DATABASE CONNECTION
     */
    if ( is_object($conn) && $configs['data_mocking'] === false ) {
        
        /* @var $sql: same SQL shell scripting is using. */
        include __DIR__ . "/module/$ns/src/$ns/Model/acmeOrdersSql.php";
        
        for( $i=0; $i<count($uniqueUkeys); $i++ ) {
            $ukey = $uniqueUkeys[$i];
            
            for( $e=0; $e<$rcnt; $e++ ) {
                if ( key( $params[$e] ) == $ukey ) { // rule node id matched user id.
                    
                    $stmt = $conn->prepare( trim( $sql ) );
                    $stmt->execute(array(
                         (int) $params[$e][$ukey]['plus_days'],
                         (int) $configs['rgo_conf']['ignore_after_days'],
                         (int) $params[$e][$ukey]['client_id']
                     ));

                    if( $stmt !== false ) {
                      while( $row = $stmt->fetch(\PDO::FETCH_ASSOC) ) {
                        if ( !empty($row['order_id']) ) $reportString = $row['client_name'] . " may have a past due orders" .
                            " for order id " . $row['order_id'] . " with a picked up date " . $row['rgo_pickup_date'] ." \n\r";
                        $foundResult[] = [
                            'user_id'  => $ukey,
                            'result'   => $row,     // empty is valid!
                            'report'   => $reportString,
                            'email'    => $params[$e][$ukey]['user_email']
                        ];  
                     }   // close while
                   }    // close if false resultset
                 }      // if keys dont match, do nothing move pointer to next.
              }         // close ruleParams inner loop
           }            // close usersArray outer loop
           
           
           /* Step 5 Database Send the consolidated report to each user */
           foreach( $uniqueUsers as $key => $fcnt ) {
               $success =sendUserReport($foundResult, $key, $fcnt);
               if ( $success === true ) {
                   continue; // to next user.
                   
               } else {    // report the error and continue on to the next user.
                   error_log ( __LINE__ . ": scheduled sendUserMail returned errors.\n" );
                   error_log( __LINE__ . " Error: " . $success[1] . ", Additional Output: " . $success[2] );
                   
               }
               
           }
           
              
    /* Step 4.2 WITH MOCK DATA: This block will run when mock data is true.
     * pass in the usersArr to return the same result but from a CSV file.  
     * Not all clients have mocked data, see acme/tmp/test_data.csv
     * @return array $result
     */   
    }  else if ( $configs['data_mocking'] === true && $rcnt > 0 ) {
        
        include __DIR__ . "/mockdata.php";
        $numClients = 3;  // only pull first three
        
        // instantiate mock data's foundResult array. same prototype as db
        for ( $i=0; $i<$numClients; $i++ ) {
            $ukey = $usersArr[$i];
            $foundResult[$i] = [
                'user_id'   => $ukey,
                'result'    => scheduleCheckMockData( $dbKey, $params[$i][$ukey]['client_id'] ),
                'report'    => "",
                'email'     => $params[$i][$ukey]['user_email']
            ];
            
            // concat the report, empty results are valid
            if ( !empty($foundResult[$i]['result']) ) {
                $reportString .= $foundResult[$i]['result']['client_name'] . 
                " may have a past due deliver for order id " . $foundResult[$i]['result']['order_id'] . 
                " with a picked up date " . $foundResult[$i]['result']['rgo_pickup_date'];
            }
            $foundResult[$i]['report'] = $reportString;
            $reportString = ''; // reset to empty  
        }
        
        /* 
         * Step 5 Mock Data Send the consolidated report to each user 
         */
        foreach( $uniqueUsers as $key => $fcnt ) {
            $success =sendUserReport($foundResult, $key, $fcnt);
            if ( $success === true ) {
                continue; // to next user.
                
            } else {    // report the error and continue on to the next user.
                error_log ( __LINE__ . ": scheduled sendUserMail returned errors.\n" );
                error_log( __LINE__ . " Error: " . $success[1] . ", Additional Output: " . $success[2] );
                
            }
        }
        
    } else {    // empty rules param means no rules were scheduled for this time.
        error_log( __LINE__ . ": scheduled ran its cron job for $interval interval.\n" );
        error_log( __LINE__ . " No rules were scheduled to run at this time. Ending process here." );
        exit();
        
    }
    
} else {        // error in JSON file, users and rules are a mismatch.
    error_log( __LINE__ . ": scheduled ran its cron job but users and rules are mismatched.\n" );
    error_log( __LINE__ . " check the JSON file $service.json for invalide json." );
    exit();
    
}

//////  DONE


/* sendUserReport consolidates foundResults email reports from a users.
 * foundResults can have an empty [result] array.
 * 
 * @param array $foundResult see header docblock for prototype.
 * @param string $key this users rule node id  ie. rgo_1b10db...
 * @param int $fcnt number of rules the process checked for this user.
 * @return array $success true on success | array on false
 * * when false: Array[ 0=>false, 1=>error_message, 2=>additional_output(if any) ]
 */
function sendUserReport( array $foundResult, string $key, int $fcnt ) {
    error_log( __LINE__ . " schedulecheck sendUserReport processing user " .
        " $key which occurs $fcnt times." );
    
    $success = false;
    $emailReport = "";
    $t = 0;
    $last = "";  // compare client id to last id processed
    
    // assemble this users reports
    for( $j=0; $j<count($foundResult); $j++ ) {
       
       if( $k = array_search( $key, $foundResult[$j] ) ) {
           if( $foundResult[$j]['user_id'] == $key && 
               $last != $foundResult[$j]['result']['client_id'] ) {
                    $t++; // new client result set, increment total count
                    $last = $foundResult[$j]['result']['client_id'];
           }
           
           $emailReport .= (!empty( $foundResult[$j]['report'] )) ? $foundResult[$j]['report'] : '';
           $emailAddress = $foundResult[$j]['email'];
           $emailName = $foundResult[$j]['user_id'];
           
        } else {
           continue;
        }
        
   }
   
   /* we should have a complete report, email it to this user */
   if( $t == $fcnt ) {
       
       $success = sendmail( $emailAddress, $emailName, $emailReport );
       
       if( $success === true ) {            // reset values for the next user
           $emailReport = "";
           $emailAddress = "";
           $emailName = "";
           
       } else if (is_array($success) ) {    // false if failed.
           $success = $success[0];
           error_log( __LINE__ . ": scheduled:sendUserResport sendmail returned errors.\n" );
       }
       
   } else {
      error_log( __LINE__ . ": scheduled sendUserReport counts" .
                " didn\'t match up, something went wrong.");       
   }
   
   return $success;
   
}


/*
 * @TODO 20180507 Remove before deploy
 * thisUserRuleIds function is a mapreduce algo to eval individual users.  
 * This allows the process to handle individual users, one at a time.
 * @return array $user an indexed array of this users id 
 * which totals the number of rules being checked for that user.
 */
function thisUserRuleIds(array $users, $thisUser)
{   
    return array_reduce($users, function ($acc, $u) use ($thisUser) {
        if ( $u != $thisUser ) { return $acc; }
        return array_merge( $acc, [ $u ] );
    },[]);  
}

?>