<?php
/**
 * Quick Check Factory | vendor/acme/src/acme/utilities/QuickCheckFactory.php
 *
 * @package Acme\Utilities
 * @subpackage QuickcheckFactory
 * @author Ron White, ronwhite562@gmail.com
 * @version 1.0, [Dev-Master]
 * @since 2018-04-19
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Acme\Utilities;

/**
 * QuickcheckFactory
 * Utility service for Notification module that pre processes the  
 * Quick Check reporting and executes the sendmail program.
 * Using Unix sendmail because it allows the process owner to send mail
 * will headers from the sendmail environment.
 *
 * Note: uses Type Return Declaration(:) which was added in PHP 7
 */

class QuickcheckFactory extends QuickcheckAbstractFactory
{
    
    public function quickcheckService(
        string $from,
        array $quickCheckArr, 
        string $scriptPath, 
        string $report ) : ResponseText
    {
        $service    = __METHOD__;
        $confirm    = [];
        $response   = "";   // execute response string
        $success    = 1;    // uses boolean 0 or 1 cuz mail program result is 0|1.
        $log        = __DIR__ . "/log/widgets.acme.log";
        
        /* fail fast if the email script doesnt exist */
        if ( !is_file($scriptPath) ) {
            throw new \Exception( __LINE__ .": $service looking for email script doesnt exist or is missing. exiting here.");
        }
        
        try { // assign args to email script.
            
            $sendto = ( !empty( $quickCheckArr['user_email'] ) ) ? $quickCheckArr['user_email'] : "avrjoe@acme.com";
            $name = ( !empty( $quickCheckArr['user_name'] ) ) ? $quickCheckArr['user_name'] : "Average Joe";
            $from = $from;
            
            /* process each client individually,
             * proto [0]=>[ client_id=>int, plus_days=>int, client_name=>string ]
             */
            $i=0;
            $clientNames = "";
            foreach($quickCheckArr as $value) {
                if ( is_array($value) ) {
                    $clientNames .= $value['client_name'] .", "; 
                }
                
            }
            
            
            // email the consolidated report, if exists
            // output must be redirected to a file or another output stream.
            $cmd = "php -f ".escapeshellcmd($scriptPath);
            $cmd .= " ".escapeshellarg($sendto);
            $cmd .= " ".escapeshellarg($from);
            $cmd .= " ".escapeshellarg($name);
            $cmd .= " ".escapeshellarg($clientNames);
            $cmd .= " ".escapeshellarg($report);
            $cmd .= " 2>&1 | tee -a /tmp/widgets_sendmail.log";
           
            
            /* execute quickcheck.php script which forks additional processes
             * quickcheck script logs success to /tmp/widgets_sendmail.log file.
             * @return array $confirm indexed array [0] is confirmation
             * @return int $success 0 on no errors, 1 on errors. 
             */
            exec( $cmd, $confirm, $success );
             
            if( !empty($confirm[0]) ) {
                foreach( $confirm as $str ) {
                    if(is_string($str)) { $response .= $str."\n"; } 
                    else if (is_array($str)) {
                       $response .= array_reduce( $str, function($response, $item) {
                                $response .= $item."\n";
                                return $response;
                        }); 
                   }
                }
                error_log($response);
                return new ResponseText( $response );
                exit();
                
            } else {
                $success = 1;    // matching Unix mail STDER
                throw new \Exception( __LINE__ ." $service Sendmail Execute Warning: $sendto QuickCheck report may not have sent." );
            }
            
        } catch ( \Exception $e ) {
            $response = $service . " Status: $success Errors: " . $e->getMessage();
            error_log($response);
        }
        // return new ResponseText( $response );
    }

}


class ResponseText extends Text
{
    public $text; // usage responseText->text
    
    public function __constuctor( Text $string ) {
        return $this->text = $string;
    }
}

?>