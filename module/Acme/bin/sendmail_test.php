<?php
define('LOGFILE', '/../tmp/mail_log');
define('TO_EMAIL',"avrjoe@acme.com");
define('FROM_EMAIL',"avrjoe@acme.com");
$timestart = microtime(true);

$log = fopen( LOGFILE, 'a+' );
$datetime = date('Y-m-d: H:i:s', time());
fwrite( $log, "$datetime start sendmail test:\n" );

$to = TO_EMAIL;
$subject = "Container Email Test 2";
$message = "Testing the sendmail functionality.";
$header = "From: " . FROM_EMAIL . "\r\n" .
    "Reply-To: " . FROM_EMAIL . "\r\n" .
    'X-Mailer: PHP/ ' . phpversion();

$success = mail( $to, $subject, $message, $header );
$timestop = ( microtime(true) - $timestart );

$output = [
    $success,
    $message,
    "Message send on: $datetime",
    "Mail sent and resturned: $success"
];

fwrite( $log, "Mail sent and returned: $success\n" );
fwrite( $log, "The process took $timestop" );
fwrite( $log, "Additional: " . file_get_contents('php://stdin') );
fwrite( $log, "\nEnd======================\n" );
fclose( $log );

if( $success === true ) {
    error_log("sendmail_test thinks it succeeded! The process took $timestop to complete.");
    
} else if ( $success === false ) {
    error_log("sendmail_test may have failed! The process took $timestop to complete.");
    
}

echo "All Done!";

?>