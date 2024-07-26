<?php 
error_reporting(E_ALL);
define('FROM_EMAIL',"avrjoe@acme.com");
/**
 * Sendmail | Acme/bin/sendmail.php
 * email script for cronjobs called at scheduled intervals.
 * email is only sent if there are results back from scheduledcheck process.
 * 
 * @param string $tosend the email address to send this Report
 * @param string $toname the users name to address the Email
 * @param string $report the Route Guide Order the were found with null deliver dates.
 * @return mixed true on success, array of errors on fail.
 */
function sendmail( string $tosend, string $toname, string $report )
{
    $success = true;
    $output = [];   // logging stream if active
    $errors = [];
    if ( !filter_var($tosend, FILTER_VALIDATE_EMAIL) ) $success = false;
    if ( !strlen($report) > 10 ) $success = false;
    
    if ( $success === true ) {
        
        // construct header
        $from = FROM_EMAIL;
        $subject = "RGO Check Report";
        $log = '/../tmp/sc.txt';
        $datetime = date( 'Y-m-d: H:i:s', time() );
        $header = "From: " . FROM_EMAIL . "\r\n" .
                  "Reply-To: " . FROM_EMAIL . "\r\n" .
                  'X-Mailer: PHP/ ' . phpversion();
        
        // construct email body
        escapeshellcmd( $message = "Hello $toname, \n\r
            Ran the automated Route Guide Order missed delivery report on your cleint(s): \n\r
            $report
            \n\r
            To disable this report or manage your notification rules, use the Acme Widget 
            app Notifications and select 'Disabled' under the 
            Manage Notifications > Edit Notifications: Rule Active column.\n\r
            \n\r
            Have a great day! \n\r
            Average Joe"
       );

       $fp = fopen($log, 'a');
       
       /* send the email */
       if ( is_string($message) ) {
           
           error_log( $datetime . ': sending mail to ' . $tosend );
           $success = mail( $tosend, $subject, $message, $header );
           
           if( is_resource($fp) ) { // log sent mail
               fwrite( $fp, "$datetime : Report emailed to $tosend and returned $success\n" );
               fwrite( $fp, "Additional output: " );
               fwrite( $fp, STDOUT . "\n" );
               fwrite( $fp, "END ======================\n" );
           }
        
        } else { // return an error
            $success = false;
            $errors = [
                $success,
                __LINE__ . ': email or messsage had no body.',
            ];
            
            if( is_resource($fp) ) {
                fwrite( $fp, "$datetime : Errors on email to $tosend which returned $success\n" );
                fwrite( $fp, "Additional output: " );
                fwrite ($fp, STDERR);
                fwrite( $fp, "\nEND ======================\n" );
            }
        }
        fclose($fp);
        
    } else {
        $success = false;
        $errors = [
            $success,
            __LINE__ . ': either the email address in not valid or the report is empty.',
        ]; 
    }
    
    return ( $success === true ) ? $success : $errors;
    
}
?>