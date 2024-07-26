<?php
namespace Acme\Utilities;
use \Exception;  // anchor Exception to root(SPL) namespace
/**
 * Acme\Utilities | vendor/acme/utilities/src/AcmeFtp.php
 * @package Acme\Utilities
 * @subpackage AcmeFtp
 * @author ron white, ronwhite562@gmail.com
 * @version 1.0
 * @since 2020-10-30
 *
 * depends on PHP 7.1 libraries openssl ^1.0 and libssh2 ^1.9.0
 * confirm both are compiles and enabled for PHP-CLI
 * * php -i | grep -in [ ssh2, openssl ]
 *
 * @param string  $host the remote IP or canonical name(CNAME)
 * @param int  $port the remote port, default is 22
 * @param string  $protocol the client making the connection, must be one of
 *   PHP configured protocols https, ftps, ssh2.shell, ssh2.exec, ssh2.scp, ssh2.sftp
 *
 * usage $ftpClient = new AcmeFtp($host, $port, $protocol, $pubkey=false);
 */

/**
 * AcmeFtp
 * This package was created to use different connection protocols
 * to transfer files between two servers. 
 * SSH2 is the preferred connection type.
 */
 class AcmeFtp
 {
     /**
      * @property $connection resource
      *
      */
     private $connection;

     /**
      * @property acquired file transfer subsystem
      * can be sftp, scp or ftp.
      * passes private property connection by reference
      */
     protected $client;

     /**
      * @property $pubkeypath;
      * path (file system) to authorized users RSA public key.
      * only required when connection protocol is sftp
      */
     protected $pubkeypath;


     /**
      * @property $host URL of target server
      * passed to constructor
      */
     protected $host;


     /**
      * @property $port target server connection port
      * passed to constructor either 21 if FTP or 22 if sFTP
      */
     protected $port;


     /**
      * @property $protocol the connection protocol to use
      * passed to constructor either ssh2 or scp or ftp
      */
     protected $protocol;


     /**
      * @property int $usepkey 1 use/requires RSA PublicKey and sftp for process owner.
      *   -1 allows any user with an ftp login password.
      */
     protected $usepkey;


     /**
      * class constructor called on connection init
      * @param string $host  required target server domain or ip address
      * @param int $port  required target server ftp port
      * @param string $protocol  required connection type protocol, sftp or ftp available
      */
     public function __construct( string $host, int $port, string $protocol )
     {
            $this->host         = $host;
            $this->port         = $port;
            $this->protocol     = $protocol;

            switch ( $this->protocol ) {
                // sftp with/without PubKey
                case "sftp" : $this->connection = ssh2_connect($this->host,$this->port,array('hostkey'=>'ssh-rsa'));
                    break;

                // SPL ftp no special requirement
                case "ftp"  : $this->connection = ftp_connect($this->host);
                    break;

                default : $this->connection = ssh2_connect($this->host, $this->port);
            }

            if(! $this->connection ) {
                throw new \Exception(__LINE__.":".__CLASS__.": couldnt connect to ".$this->host." on port ".$this->port);
            }
     }


     /**
      * setters/getters for remaining class properties
      *
      * @param int $usepkey defined in defaults
      * @method setUsePubKey setter for hydrating property usepkey.
      * @method getUsePubKey getter for returing properties value
      *         which is null unless setter was called first
      */
     protected function setUsePubKey(int $usepkey) { $this->usepkey = $usepkey; }

     public function getUsePubKey() { return $this->usepkey; }


     /**
      * @param string $pkeypath, configured file system path to RSA public key
      * @method setPubKeyPath validates RSA Public Key file
      *         will reset usepkey property if PublicKey file does not exists
      * @method getPubKeyPath getter for returning valid path or NULL
      */
     protected function setPubKeyPath(string $pkeypath)
     {
         $m = __LINE__.":".__CLASS__.":";
         $scpowner = get_current_user(); // process owner

         if( file_exists($pkeypath) ) {
             $this->pubkeypath = $pkeypath;
             $m .=  'RSA pub key found, setting path to key for '.$scpowner.'!\n';
         }
         else { // construct the path, from server filesystems "/"
             $path      = explode(realpath(__DIR__));
             $trypath   = "/".$path[0]."/".$scpowner."/.ssh/id_rsa.pub";

             if( ! file_exists($trypath) ) { // resets to no public key
                 $this->setUsePubKey(-1);
                 $this->usepkey     = $this->getUsePubKey();
                 $this->pubkeypath  = NULL;
                 $m .= 'RSA public key not found, try username and password authentication.\n';

             } else {
                 $this->pubkeypath = $trypath;
                 $m .=  'RSA pub key path constructed for '.$scpowner.', using key path!\n';
             }
         }

        // debug
        error_log($m,3,__DIR__."/../tmp/debug.log");
     }
     // getter
     public function getPubKeyPath() { return $this->pubkeypath; }


    /**
     * @param object connection resource id
     * @method setClient modifies the connection object(passed by reference)
     * @method getClient returns an instance of the connection object
     */
     private function setClient($conn) { $this->client = $conn; }
     // getter
     protected function getClient() { return $this->client; }


     /**
      * @method login
      * passes the user name and password to the connection object
      * @param string $user the FTP user on remote server
      * @param string $pass the FTP users password
      * @throws Exception on login fail or true on success
      */
      public function login( $user, $pass, $usepkey=-1, $pkeypath=NULL) 
      {
        $m = __CLASS__.":".__METHOD__.": ";
        if (! $this->client) { // fail fast
            $m .= __LINE__.": critical stop error, could not initialize sftp or ftp client for $user!\n";
            $this->debugOutputBuffer( $m, $this->connection );
            throw new \Exception(__LINE__.": "."ERROR: $m couldnt initialize FTP sub system for " . $this->protocol);
        }

         $this->setUsePubKey($usepkey);
         switch ( $this->protocol ) {

             case "sftp" : // use sftp and SSH sub system

                 if ( $this->usepkey === 1 ) { // using pub key
                     $this->setPubKeyPath($pkeypath);
                     $privatek = $this->getPubKeyPath();

                     error_log("realpath to private key " . $privatek,3,__DIR__."/../tmp/debug.log");
                     $publick  = $privatek.".pub";

                     if ( ! ssh2_auth_pubkey_file( $this->connection, $user, $publick,
                         $privatek, $pass ) ) {
                             throw new \Exception(__LINE__.": "."$m Public Key authentication failed!");
                         }
                         $m .= " public key accepted, sftp connection open for transaction.\n";

                 }  else  { // using password auth
                    if (! ssh2_auth_password( $this->connection, $user,$pass ) )
                        throw new \Exception(__LINE__.": "."$m user $user couldnt login(sftp) at host " . $this->host);

                        $m .= " username and password accepted, sftp connection open for transaction.\n";
                 }

                $this->client = @ssh2_sftp($this->connection);
                break;

            case "ftp" : // using POFTP

                if (! @ftp_login( $this->connection, $user, $pass ) )
                    throw new \Exception(__LINE__.": "."$m user $user couldnt login(ftp) at host " . $this->host);

                    $m .= " username and password accepted, SPL ftp connection open for transaction.\n";

                 $this->client = $this->connection;
                 break;
         }

         //debug
         error_log($m,3,__DIR__."/../tmp/debug.log");
         return true;
     }


     /**
      * @method downloadFile
      * @param string $remoteFile path to remote file to read from
      * @param string $localFile path to local file to write to
      * @throws Exception on transaction fail
      */
     public function downloadFile($remoteFile,$localFile) {

         $data      = "";
         $handle    = @fopen($localFile,'w');
         $m         = __CLASS__.":".__METHOD__.": ";

         switch ( $this->protocol ) {

             case "sftp" :
                 $stream = @fopen("ssh2.sftp://$this->client$remoteFile",'r');
                 if(! $stream ) throw new \Exception(__LINE__.": $m sftp couldnt open remote file $remoteFile for reading");

                 $data = stream_get_contents($stream);
                 fclose($stream);
                 unset($this->client);

                 fwrite($handle, $data); // writes to local file
                 break;

             case "ftp" :
                 if(! ftp_fget($this->client,$handle,$remoteFile,FTP_ASCII,0) )
                     throw new \Exception(__LINE__.": $m ftp couldnt open remote file $remoteFile for reading");

                 $data = fread($handle);
                 ftp_close($this->client);
                 break;
         }

         fclose($handle);
         echo $data."\n";
     }


     /**
      * @method uploadFile
      * @param string $remoteFile path to remote file to write to
      * @param string $localFile path to local file to read from
      * @throws Exception on transaction fail
      */
     public function uploadFile($remoteFile,$localFile,$fowner,$fgroup) 
     {
         $data      = "";
         $handle    = @fopen($localFile,'r');
         $m         = __CLASS__.":".__METHOD__.": ";
        if (! $handle) { // fail fast
            $m .= __LINE__.": critical stop error, no file reference for $localFile, owner $fowner!\n";
            $this->debugOutputBuffer( $m, $this->connection );
            throw new \Exception(__LINE__.": "."ERROR: $m no filepointer reference for: " . $localFile);
        }

         switch ( $this->protocol ) {

             case "sftp" :
                 $stream = @fopen("ssh2.sftp://$this->client$remoteFile",'w');
                 if(! $stream ) throw new \Exception(__LINE__.": $m sftp couldnt open remote file $remoteFile for writing");

                 $data = @fread($handle,filesize($localFile));
                 if ($data === false) throw new \Exception(__LINE__.": $m sftp couldnt read local file $localFile!");

                 if (@fwrite($stream, $data) === false)
                     throw new \Exception(__LINE__.": $m sftp didnt write out remote file $remoteFile!");

                 $data = "sftp uploaded file ". end(explode("/",$localFile));
                 $m .= $data;
                 fclose($stream);
                 unset($this->client);
                 break;

	     case "ftp" :
		 chown($remoteFile,$fowner);
		 chgrp($remoteFile,$fgroup);
		 $stat = stat($localFile);
		 $uid = posix_getpwuid($stat['uid']);

                 if(! ftp_fput($this->client,$remoteFile,$handle,FTP_ASCII,0) )
                     throw new \Exception(__LINE__.":".__CLASS__.": ftp failed to write new file $remoteFile to remote!");

                     $data = "Uploaded file ". end(explode("/",$localFile)) . " with owner id $uid\n";
                     $m .= $data;
                     ftp_close($this->client);
                     break;
         }
         fclose($handle);

         //debug
         error_log($m,3,__DIR__."/../tmp/debug.log");
         echo $data."\n";
     }


     /**
      * debugOutputBuffer
      */
     public function debugOutputBuffer($message, $object=null)
     {
         $str = "";
         ob_start();
         echo __METHOD__.": message: $message";
         if ( $object != null ) print_r($object);
         $str = ob_get_clean();

         error_log($str."\n",3,__DIR__."/../tmp/debug.log");
     }

 }
