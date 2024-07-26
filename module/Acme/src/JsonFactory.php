<?php 
namespace Acme\Utilities;
/**
 * @author Ron White, ronwhite562@gmail.com
 * @version 1.0, [Dev-Master]
 * @since 2018-04-19
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * 
 * @package JsonFactory
 * Accepts data input (file or data object) and converts the content 
 * in to JSON objects. Each notification service can have a different 
 * list of properties but requires the same scheme which is:
 * * Object {
 * * * <service_node>: Array [ 
 * * * * Object { <rulekey_userid>: Properties { <key>:<value> } } 
 * * * ]
 * * }
 * 
 * Note: uses Type Return Declaration(:) which was added in PHP 7
 */

class JsonFactory extends RulesAbstractFactory
{
    
    /**
     * @method convertPostToString
     * The implementation method for RulesAbstractFactory::Text
     * {@inheritDoc}
     * @see \Acme\Utilities\RulesAbstractFactory::convertPostToString()
     */
    public function convertPostToString(array $post) : Text
    { 
        $JsonString = $this->serializeJsonText($post);
        return new JsonText($JsonString);
    }
    
    
    /**
     * @method loadJsonFromFile
     * Read only this services array of node objects. Does not write back to
     * notifications file, use Acme\Utilities\WriterFactory for that.
     *
     * @param string $filePath path to JSON source file
     * @param string $method how to open file, "r" for reading, "w" write/replace, etc
     * @param string $user_id (optional) if passed, return only that users rules,
     * * otherwise return all rules.
     *
     * @return array $handle all rules or only users if user_id is passed
     * * proto $handle[0]['rgo_1d10...bb024']
     */
    public function loadJsonFromFile( $filePath, $method, $user_id=null ) 
    { 
        $service = "JsonFactory->loadJsonFromFile";
        $status = true;
        $handle = [];
        $array = [];
        
        try {
            if ( $file = fopen($filePath, $method) ) {
                $contents = fread($file, filesize($filePath));
                fclose($file);
                clearstatcache();
            } else {
                $status = false;
                throw new \Exception( __LINE__ . ": Couldn't open a file for reading.");
                
            }
            
            if ( strlen($contents) > 0 ) {
                $currentRulesObj = json_decode( $contents );
                $currentRulesKey = ( is_object($currentRulesObj) ) ? key($currentRulesObj) : null;
                $handle = $currentRulesObj->$currentRulesKey; // all rules
                
            } else if( !is_object($handle) ) {
                $status = false;
                throw new \Exception( __LINE__ . ": Couldn't assign object to handle.");
                
            }
                
           for( $i=0; $i<count($handle); $i++) {                // each rules outer loop
               foreach( $handle[$i] as $keyObj => $arrObj ) {   // each rule nodes inner loop
                    $rid = explode("_", $keyObj);               // ie rgo_1d10...

                    /* match this users id with the user node keys */
                    if( $user_id !== null && $user_id == $keyObj ) {
                        $userArray[][$keyObj] = get_object_vars($arrObj);
                    
                    } else { // assign no user id passed, get all rules
                        $allArray[][$keyObj] = get_object_vars($arrObj);
                        
                    }
                }   // close inner
           }        // close outer
            
           if ( $status === true ) {
                $handle = ( $user_id !== null ) ? $userArray : $allArray;
                $arrCnt = count($handle);
            
            } else {
                throw new \Exception( __LINE__ . ": Errors occurred while slicing up the rules array." );
                
            }
            
        /* pass errors back for reporting */
        } catch( \Exception $e ) {
            error_log( "$service errored out " . $e->getMessage() );
            $errors[] = [
                'message' => $service . " " . $e->getMessage(),
                'errors' => -1,
                'status' => $status
            ];
            
            return $errors;
        }
        
        // error_log( "$service completed, returning array with $arrCnt elements." );
        return $handle;
        
    }
    
    
    /**
     * @method serializeJsonText
     * Accepts POST array input and serializes a complete 
     * notification rule based on the service components scheme.
     * 
     * Scheme Note: serialized strings are nested inside the service node array
     * example: { Notifications: [ { <this_node_string> },...] }
     * 
     * @param array $postArray, users input values. requires a user id.
     * @return string $this, proto { <rulekey_userid>: { <key>:<value> } }
     */
    protected function serializeJsonText($postArray) 
    {
        $service = "JsonFactory->serializeJsonText";
        $errors = null;
        $string = null;
        
        try 
        {
            if ( is_array($postArray) && !empty($postArray['user_id']) ) {
              
                $this->string = '{ ';
                $this->string .= '"'. $postArray['ruleNodeName'] .'": { ';
                unset($postArray['ruleNodeName']);
            
                foreach( $postArray as $key => $value ) {
                    switch($key) {
                        case "user_id" :
                            $this->string .=  '"user_id": "' . $value . '", ';
                        break;
                    
                        case "client_id" :
                            $cparts = explode("~", $value);     // <id>~<client_name>
                            $this->string .= '"client_id": "' . $cparts[0] . '", ';
                            $this->string .= '"client_name": "' . $cparts[1] . '",';
                        break;
                    
                        case "plus_days" :
                            $this->string .= '"plus_days": "' . $value . '", ';
                        break;
                    
                        case "check_status" :
                            $this->string .= '"check_status": "' . $value . '", ';
                        break;
                        
                        case "user_email" :
                            $this->string .= '"user_email": "' . $value . '", ';
                        break;
                        
                    }
                }   // close foreach
            
                // add additional elements
                $this->string .= '"timestamp": "' . time() . '", ';
                $this->string .= '"active": "enabled" ';
                $this->string .= "}";       // close this rule
                $this->string .= "}";       // close this nested object
                
                if( !strlen( $this->string ) > 10 ) throw new \Exception( 
                        __LINE__ .": serialized string is too short." ); 
            
            } else {
                throw new \Exception( __LINE__ . " requires post array and user id but one wasn\'t passed." ); 
            }
            
        } catch( \Exception $e ) {
            error_log( "$service Errors: " . $e->getMessage() );
            $errors = "$service Errors: " . $e->getMessage();
            return $errors;
        }
        
        return $this->string;
    }
    
}


class JsonText extends Text
{
    public $string;
    public function __constuctor(Text $string) {
        return $this->string = $string;
    }
}
?>