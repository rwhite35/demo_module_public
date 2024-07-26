<?php
error_reporting(-1);
/**
 * executable script for transferring files via ftp/sftp to a 
 * target directory on the remote server
 *
 * depends on Acme\Utilties\AcmeFtp
 * @see Acme\Utilities\AcmeFtp for info
 *
 * @param argv[1] required, ACH Transaction document file name
 * * includes extension  <sender-id>_<ach-ctr-num>_<file-version>.<ext>
 * * example: acme_1001_v4.EDI
 *
 * @param argv[2] required, remote folders owner and group names
 * * example: BanksCloud:sftpchroot
 *
 * usage:  php -f ../vendor/acme/bin/sftpScrip.php $FILENAME $OWNERGROUP
 * * assumes called from public/ in the project root
 *
 */
chdir(dirname(__DIR__,3));

/* autoload PSR4 files from ACH application
 * and vendor/ dirs. these are required dependencies,
 * if failing confirm vendor/ installed and resources exist.
 */
if (! file_exists('vendor/autoload.php')) {
    throw new RuntimeException(
        'Unable to load application.' . PHP_EOL
        . '- Type `composer install` if you are developing locally.' . PHP_EOL
        . '- Type `vagrant ssh -c \'composer install\'` if you are using Vagrant.' . PHP_EOL
        . '- Type `docker-compose run apigility composer install` if you are using Docker.'
        );
}
require_once 'vendor/autoload.php';
require_once 'vendor/acme/src/AcmeFtp.php';
use Acme\Utilities\AcmeFtp;

// for local development only,
// $localArr = require 'config/local.php';
$localArr = require 'config/default.php';
$dt = date("Y-m-d H:i:s", time());
$log = realpath(__DIR__) ."/../tmp/debug.log";


/**
 * @param string filename
 * a fully qualified path/file name including file extension
 * @var string $localFile path/file to test
 * @var string $remoteFile filesystem path/file to payload file
 */
$filename = ($argv[1]) ? $argv[1] : "acme_1000_v5.EDI";
$chownnames = ($argv[2]) ? explode(":",$argv[2]) : ["nobody","nobody"];
$fowner = $chownnames[0];
$fgroup = $chownnames[1];
$localFile  = 'data/'.$filename;
$remoteFile = $localArr['ftp']['remotefpath']."/".$filename;
// debug
$m = __LINE__.": $dt: localFile $localFile with remoteFile $remoteFile.\n";


/**
 * configured ftp connection parameters
 * see config/local.php for development values
 */
$ftpuser    = ($localArr['ftp']['ftpuser']) ? $localArr['ftp']['ftpuser'] : "anonymous";
$ftppwd     = ($localArr['ftp']['ftppwd']) ? $localArr['ftp']['ftppwd'] : "";
$usepkey    = $localArr['ftp']['usepubkey'];
$pkeypath   = $localArr['ftp']['pubkey'];

$host       = $localArr['ftp']['ftphost'];
$protocol   = $localArr['ftp']['protocol'];
$port       = ($protocol == "sftp") ? 22 : 20;
$m .= __LINE__." initializing connecting to host $host, on port $port\n";

$ftpClient = new AcmeFtp($host, $port, $protocol);
$ftpClient->login($ftpuser, $ftppwd, $usepkey, $pkeypath);

if (!$ftpClient instanceof AcmeFtp) $m .= __LINE__.": WARN: Acme\Utilities\AcmeFtp didnt construct an FTP client!\n";
error_log($m,3,$log);

try {

    // first download test file to get Owner:Groups UID
    // $ftpClient->downloadFile("derp.txt");

    // uploadFile stdout on success
    echo $ftpClient->uploadFile($remoteFile,$localFile,$fowner,$fgroup);
    echo "\ndone!";

} catch(\Exception $e) {
    // debug
    $m .= __LINE__.": Error Caught: " . $e->getMessage() . "\n";
    error_log($m,3,$log);

    // stdout
    echo __LINE__.": Error on sftp test:\n";
    echo $e->getMessage();
    echo $e->getTrace();
}
