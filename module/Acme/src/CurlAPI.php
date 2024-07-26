<?php
namespace Acme;
/**
 * CurlAPI allows a POST or PUT request make server to server calls when a 
 * collection is not defined and the resource just needs to query some data source.
 * 
 * THIS SHOULD NOT REPLACE Collections, as Collections are more robust and fully
 * integrated with the API system. This is more for convenience than utility.
 * 
 * Similar to executing a curl request from the command line. Example
 * curl http://api.acme.com:8080/oauth \
 * -d 'grant_type=client_credentials \
 * &client_id=acmemobile&client_secret=<clear_text>'
 * 
 * For cURL options see https://secure.php.net/manual/en/function.curl-setopt.php
 * 
 * @return String | mixed $results, query result can be string or object.  
 * example: {"access_token":"9127...9cf1","expires_in":7200,"token_type":"Bearer","scope":"oauth"}
 */

use Laminas\Crypt\Password\Bcrypt;
use Laminas\EventManager\EventManager;
use Laminas\ApiTools\ApiProblem\ApiProblemResponse;
use Laminas\ApiTools\ApiProblem\Exception\DomainException;
use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\Config\Writer\WriterInterface as ConfigWriter;
use DateTime;

class CurlAPI
{
    
    protected static $timestamp;
    
    
    /**
     * CreateToken uses cURL to create a valid OAuth token 
     * by calling /oauth (vendor/zf-oauth). The /oauth endpoint will insert
     * a new record in oauth_access_tokens table.
     * 
     * @param string $url
     * @param array $data
     * @param array $headers
     * @return mixed $timestamp on success and false on fail
     */
    public static function CreateToken(
        $url,               // fully qualified URL with endpoint route parts
        $data=[],           // form-data as assoc array
        $headers=[]         // Accept, Content-Type, Authorizaton fields
        ){
        
        self::$timestamp = new DateTime;
        $datetime = self::$timestamp->format('Y-m-d H:i:s');
        
        // open file reference for output logging
        $fp = __DIR__ ."/log/api.acme.log";
        $fpath = (is_file($fp))? $fp : "/tmp/api.acme.log";
        $handleOut = fopen( $fpath, 'a+' );
        
        // DomainException Errors
        $exception = new DomainException;
        
        // end part should be the resource route (not a route id)
        $parts = explode("/", $url);
        $route = end($parts);
        
        $m = "\n$datetime: CurlAPI::CreateToken working with route: $route\n";
        error_log($m, 3, $fpath);
        
        switch($route) {
            case "oauth":
                $post = json_encode($data);
                $bytes = mb_strlen($post, '8bit');
                array_push( $headers, "Content-Length: $bytes" );
                array_push( $headers, "Content-Type: application/json" );
                
                ob_start();
                echo __LINE__ .": ".__METHOD__.": route.oauth headers:\n";
                print_r($headers);
                echo __LINE__ .": ".__METHOD__.": route.oauth post:\n";
                print_r($post);
                
                $str = ob_get_clean();
                error_log($str."\n", 3, $fpath);
                
                break; 
        }
        
        
        // cURL execution throws DomainException
        try {
            
            $curl = curl_init();
            
            // initialize cURL session and set headers
            $defaults = [
                    CURLOPT_URL => $url,
                    CURLOPT_HTTPHEADER => $headers,
                    CURLOPT_POSTFIELDS => $post,
                    CURLOPT_NOBODY => FALSE,
                    // CURLOPT_RETURNTRANSFER => 0,    // 0 - returns result to stdout.
                    // CURLOPT_RETURNTRANSFER => 1, // 1 - returns result instead of print to screen
                    CURLOPT_TIMEOUT_MS => 200,      // milliseconds
                    CURLOPT_NOSIGNAL => 1,          // use this with timeout only
            ];
            
            curl_setopt_array($curl, $defaults);
            curl_exec($curl);
            
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $m =  "$datetime: CurlAPI::CreateToken HTTPCode: $httpCode\n";
            error_log($m, 3, $fpath);
            
            curl_close($curl);
            
        } catch( \DomainException $exception ) {
            $errorCode = $exception->getCode();
            
            if ( $httpCode == 0 ) {
                $mess =  "$datetime: CurlAPI::CreateToken thrown ApiProblem:\n";
                $mess .= "Code: " . $errorCode ."\n";
                $mess .= "Message: " . $exception->getMessage() ."\n\n";
                $mess .= "Trace: " . $exception->getTraceAsString() ."\n";
                $mess .= "AdditionalDetails: " . $exception->getAdditionalDetails();
                fwrite( $handleOut, $mess );
            }
        }
        
        // clean up references
        fclose($handleOut);
        // return self::$timestamp;
        // return $response;
        // return true;
    }
    
    
    /**
     * When client_secret is not a Bcrypt hash, but required
     * @param string $hashed, stored as Bcrypt hash string.
     */
    private function hashPassword($password)
    {    
        if (substr($password, 0, 4) != "$2y$") {
            $bcrypt = new Bcrypt();
            $hashed = $bcrypt->create($password);
            
        } else {
            $hashed = $password;
            
        }
        return $hashed;
    }
    
}