<?php 
/**
 * emails a quick check report async when run from cronjob,
 * only called if there is a result back from Notifications process.
 */
define('LOGFILE', '/../tmp/widgets_sendmail.log');
define('QC_EMAIL', "avrjoe@acme.com");
$timestart = microtime(true);

if( isset($argv) ) {
    $sciptname = $argv[0];   // calling script
    $sendto = $argv[1];      // users email
    $from = $argv[2];        // valid mailbox for bounce notifications
    $name = $argv[3];        // users name or user id if name not avail
    $client = $argv[4];      // client name(s)
    $report = $argv[5];      // report string
}

$log = fopen( LOGFILE, 'a+' );
$datetime = date('Y-m-d: H:i:s', time());
fwrite( $log, "$datetime argvs\n".implode(' ', $argv ) ."\n" );

// compose mail
$subject = "Quick Check Report";
$header = "From: " . QC_EMAIL . "\r\n" .
    "Reply-To: " . QC_EMAIL . "\r\n" .
    'X-Mailer: PHP/ ' . phpversion();

// escape any shell command characters
escapeshellcmd( $message = "Hello $name, \n\r
    Ran a Quick Check report on $client co(s): \n\r
    $report \n\r
    \n\r
    Have a great day! \n\r
    Admin"
);

// send the email
if ( is_string($message) ) {
    $success = mail( $sendto, $subject, $message, $header );
    $timestop = ( microtime(true) - $timestart );
    
    $success = ($success === true)? 0 : 1;
    
    $output = [
        $success,
        $message,
        "QuickCheck report emailed on: $datetime",
        "Mail returned status: $success"
   ];
   
   if ( $output[0] == 0 ){ echo $output[2] .", ". $output[3] ."\n"; }
    
   // log result to /tmp/widgets_sendmail.log
   fwrite( $log, "Mail sent and returned status: $success\n" );
   fwrite( $log, "The process took $timestop\n" );
   fwrite( $log, "Additional Info: " . file_get_contents('php://stdin') );
   fwrite( $log, "\nEnd======================\n" );
   fclose( $log );
   
   return $success;
    
} else {
    error_log('Acme\Utilities quickcheck script failed to send email or messsage had no body.');
    
}
?>