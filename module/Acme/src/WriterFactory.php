<?php 
namespace Acme\Utilities;
/**
 * @author Ron White, ronwhite562@gmail.com
 * @version 1.0, [Dev-Master]
 * @since 2018-04-19
 * 
 * @package WriterFactory
 * Writes out data to flat file. If the file doesn't exist, the process
 * will try and create a new file.  If that fails, the process will exit with
 * errors.
 * 
 * The Scheme(json): 
 * * Object {
 * * * <service_node>: Array [ 
 * * * * Object { <rulekey_userid>: Properties { <key>:<value> } } 
 * * * ]
 * * }
 *
 */
class WriterFactory extends WriterAbstractFactory
{
    /**
     * @method writeJsonToJson
     * appends new rules to a services root node.
     * this method is called from RouteGuide writeRuleAction method
     * 
     * @param string $service, name of service ie Notifications, Invoices, etc
     * @param string $json, the json string passed from the Create New Rule
     * @param string $filePath, path to the file to open (or gets created)
     * @param string $meth, optional fopen method, could be a, r, r+, w or w+
     * {@inheritDoc}
     * @see \Acme\Utilities\WriterAbstractFactory::writeJsonToFile()
     * @return object $success is true on success or errors if false; usage $success->success
     */
    public function writeJsonToFile( 
        string $service, 
        string $json, 
        string $filePath, 
        string $meth = null 
     ) {

        $class = "WriterFactory->writeJsonToFile";
        $success = (object)[];
        $bytes = 0;
        $status = true;
        $fp = "";                                     // resource reference id
        $method = ( $meth !== null ) ? $meth : "r+";  // a, w+ or (default) r+
        
        // try to create the file if doesn't already exist
        if ( !is_file($filePath) ) {
            $fp = fopen( $filePath, "a" );
            
        } else if ( !$fp ) {
            $fp = fopen( $filePath, $method );    // should be r+ (default) or w+
            
        } else {
            $status=false;
            throw new \Exception( __LINE__ . ": couldn't open or create file for writing." );
        }
            
        // determine if the resource referenced has content already
        try {
            
            if ( is_string($json) ) {
                $contents = @fread($fp, filesize($filePath));   // may have zero byte length
                $currentRulesObj = json_decode( $contents );    // read JSON from file
                $newRuleObj = json_decode( $json );             // Create New or Edit Rules
                $newRuleKey = key($newRuleObj);                 // rule node key.
                
            } else {
                $status = false;
                throw new \Exception( __LINE__ . ": JSON wasn\'t string or file ref pointer doesn't exist.\n");
                
            }

            /* 
             * if content has zero length(false), its a new file
             * create the service node and the this rule node in one go.
             * @param $service this notification services name ie Notifications or Invoicing
             * @param $json this users new rule to append to this service 
             * See scheme in header doc block for more info.
             */
            if ( $contents === false ) {
                fwrite($fp, '{');
                fwrite($fp, '"' . $service . '" : [');
                $bytes = fwrite($fp, $json); // this users rule
                fwrite($fp, ']');
                fwrite($fp, '}');
                fclose($fp);
            }
            
            /* exit here if successfully created */
            if( $bytes > 0 ) {
                
                error_log( $class . ' wrote to file, ' . $bytes . ' bytes written.');
                $success = [
                    'rule_key' => "$service created a new file with $bytes bytes written.",
                    'errors' => '',
                    'success' => $status
                ];
                return $success;
                exit();
            }
             
            // if file did exists, and has content, just append the new rule
            if ( strlen($contents) > 0 ) {
              foreach ( $currentRulesObj as $key => $nodeObject ) {
                
                if ( $key !== $service ) { // skip to next service node
                    continue;
                    
                } else {  // match! we're in the correct service node
                    // append new rule to this service, see scheme for structure

                    @$currentRulesObj->$key[]->$newRuleKey = $newRuleObj->$newRuleKey;

                    if ( property_exists(end($currentRulesObj->$key), $newRuleKey) ) {
                        $newjson = json_encode($currentRulesObj);
                        $fp = fopen( $filePath, $method );
                        $bytes = fwrite($fp, $newjson);
                        fclose($fp);
                        
                        $success = [
                            'rule_key'  => "$service bytes $bytes written for $newRuleKey",
                            'bytes'     => $bytes, 
                            'errors'    => '',
                            'success'   => true
                        ];
                        
                    } else { // something went wrong with this rule
                        $status = false;
                        throw new \Exception( 
                            __LINE__ . ": $newRuleKey key at $i wasn't appended to service node $key.\n" 
                        );
                    } 
                }
              }     // close foreach  
           }        // close if
                      
        } catch( \Exception $e ) {
            error_log( $class . ' completed with errors ' . $e->getMessage() );
            $success = [
                'rule_key'  => $newRuleKey,
                'errors'    => $service . ": " . $e->getMessage(),
                'success'   => $status
            ];
            
        }
        
        // cleanup
        clearstatcache();
        return $success;
        
    }
    
    /**
     * @method writeArrayToFile
     * takes a complete service array and converts to JSON then writes out
     * to the target file. NOTE: this process is ATOMIC, that is it clobbers
     * the previous content and writes out new.  All users rules are preserved 
     * and only updated rules are modified. Used by EditorController updateAction.
     * 
     * @param string $service, name of service ie Notifications, Invoices, etc
     * @param string $serviceArr, the complete service nodes array with 
     * * service name as the root key.
     * @param string $filePath, path to the file. !!!! MUST already exist !!!!
     * @param string $meth, optional fopen method, could be a, r, r+, w or w+
     * {@inheritDoc}
     * @see \Acme\Utilities\WriterAbstractFactory::writeJsonToFile()
     * @return array $success is true on success and errors on fail
     */
    public function writeArrayToFile( 
        string $service, 
        array $serviceArr, 
        string $filePath, 
        $meth = null
      ) {

        $class = "WriterFactory->writeArrayToFile";
        $errors = [];
        $success = (object)[];
        $bytes = 0;
        $status = true;
        $fp = "";                                       // file pointer
        $method = ( $meth !== null ) ? $meth : "w+";    // w+ or r+
        
        // file should already already exist, if not fail fast
        if ( !is_file($filePath) ) {
           $status = false;
           $success = [
               'service_key'   => $service,
               'errors'        => $service . ' file must already exist but doesnt! returns here.',
               'status'        => $status
           ];
           return $success;
           exit();
        }
        
        try {
            if ( !array_key_exists( $service, $serviceArr ) ) {
                $status = false;
                throw new \Exception( __LINE__ . ": $service node key doesnt in JSON object." );
                
            } else {
                $json = json_encode($serviceArr);
                $bytes = strlen($json);
            
                if ( !$fp && filesize($filePath) > 0 ) {
                    $fp = fopen( $filePath, $method );
                
                } else {
                    $status = false;
                    throw new \Exception( __LINE__ . ": JSON resource reference didn't open with $method." );
                    
                }
                
                if (flock($fp, LOCK_EX) && $bytes > 0 ) {
                    ftruncate($fp, 0);              // truncate file
                    fwrite($fp, $json);
                    fflush($fp);                    // must call flush before releasing file lock
                    flock($fp, LOCK_UN);
                    fclose($fp);
                    
                    $success = [                    // Success! send back data
                        'service_key'   => $service,
                        'message'       => "$service wrote bytes $bytes to JSON file.",
                        'errors'        => "",
                        'success'       => $status  // boolean ( true | false )
                    ];
                    
                    return $success;
                    exit();
                
                } else {
                    $status = false;
                    error_log( "$class counldn\'t acquire file lock for resource.");
                    throw new \Exception( __LINE__ . ": counldn\'t acquire lock on file $filePath." );
                    
                }
            }
            
        } catch( \Exception $e ) {
            $success = [
                'service_key'   => $service,
                'errors'        => $e->getMessage(),
                'success'       => $status
            ];
        }
       
        clearstatcache();
        return $success;
        
    }
    
}
?>