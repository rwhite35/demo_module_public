<?php
/**
 * ArrayMapper.php
 *  
 * @subpackage Reflection Class Objects
 * @version 1.2, 20190829
 * 
 * Map properties from object to object using PHP Reflections class
 * Data entitied are defined in Model class and used to prototype 
 * an object structure using Reflections.  
 * 
 * Object properties are then hydrated with content from DB statements 
 * or JSON request objects.  The object can then be manipulated and used
 * for other process purposes.
 * 
 * @see com.acme.api: Tracking\V1\Rest\Milestones\MilestonesResource
 * 
 * @example $dateEntity = $this->arraymap->create($data, $formFields,
 *          "Example\V1\Rest\Resource\DataEntity");
 * 
 * @return  $ReflectionClass Object, a copy of the DataEntity
 */
namespace Acme;

use DomainException;
use InvalidArgumentException;
use Traversable;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Hydrator\ObjectProperty as ObjectPropertyHydrator;
use Laminas\ApiTools\Configuration\ConfigResource;
use DateTime;

// Acme Utilities
use Acme\MapperInterface;
use phpDocumentor\Reflection\Types\Object_;
use Laminas\Validator\Barcode\Identcode;
use Laminas\Db\Adapter\Driver\IbmDb2\IbmDb2;
use Laminas\ComponentInstaller\Collection;

/**
 * Mapper implementation using a file returning PHP arrays
 */
class ArrayMapper implements MapperInterface
{
    /**
     * @var ConfigResource
     */
    protected $configResource;
    
    /**
     * @var array
     */
    protected $mobileusersdata;
    
    /**
     * @var <Resource_Named>Entity
     * The endpoints Entity properties expected in 
     * response body.  ie ReceivedResource expect 
     * access_token, expires_in, token_type and scope.
     */
    protected $entityPrototype;
    
    /**
     * @var <Resource_Name> Form-data form fields
     * If an endpoint supports POST, PUT or UPDATE and 
     * has form fields, the Model will should also have a 
     * prototype defined which represents the form field name keys.
     */
    protected $formFieldKeys;
    
    /**
     * @var ObjectPropertyHydrator
     */
    protected $hydrator;
    
    /**
     * @var String logfile
     */
    protected $logfile;

    /**
     * @var mockArgs
     */
    protected $mockArgs;

    /**
     * @var $configs
     */
    protected $configs;
    
    /**
     * @param array $data
     * @param ConfigResource $configResource
     */
    public function __construct(
        array $data,
        ConfigResource $configResource
    ) {
        
        $this->formFieldKeys = [];
        $this->configResource = $configResource;
        
        $this->hydrator = new ObjectPropertyHydrator();
        $this->entityPrototype = (Object_::class);
        $this->logfile = __DIR__ . '/../tmp/debug.log';
        $this->configs = require(__DIR__ . "/../config/module.config.php");
        $this->mockArgs = $this->configs['acme'];
    }
    
    
    /**
     * @param array|Traversable|\stdClass $data, the data structure should match its entity.
     * @param string (optionally) $fields, form fields to map from
     * @param string (optional) $className, the resource entity to return.
     * @return Object Entity
     */
    public function create( $data, $fields=[], $className=null )
    {   
        
        $config = $this->configResource->fetch(true);
        $timestamp = new \DateTime();
        $datetime = $timestamp->format( 'Y-m-d H:i:s' );
        $uid = "";
        
        // create a new entity or just lazy load the previous
        // entityPrototype has global scope and is returned to calling resource.
        try {
            
            if ( ! $this->entityPrototype instanceof $className ) {
                    $this->setClassObject($className);
                    // debugging
                    $m = __METHOD__.": has class name: $className\n";
                    error_log( $m, 3, $this->logfile );
                
            } else {
                throw new \InvalidArgumentException(sprintf(
                    "$className object already created, hope thats what you want.",
                    __METHOD__
                    ));
            }
        
            // get this entities form fields
            if ( !empty($fields) ) $this->formFieldKeys = $fields;

            // assign data to ArrayMapper object
            if ($data instanceof Traversable) { // when traversable
                $data = ArrayUtils::iteratorToArray($data);
            
            } else if ( is_object($data) && !empty($fields) ) { // when type cast
                $data = (array) $data;     
            }
       
            if ( !is_array($data) ) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid data provided to %s; must be stdClass or Traversable',
                    __METHOD__
                    ));
            }
            
            // validate access token if expected in this entities context
            // otherwise sets a dummy token for a graceful exit
            if ( array_search("access_token", $this->formFieldKeys )
                && !isset( $data['access_token']) ) {
                    $data['access_token'] = $this->mockArgs['mockToken'];
                    throw new InvalidArgumentException(sprintf(
                        'The AccessToken provided to %s; does not match record or is invalid.',
                        __METHOD__
                        ));
                }
            
            // check for mobileusers.php data file and 
            // if doesn't exist, try and create it.
            $uid = ( isset($data['user_id']) ) ? $data['user_id'] : $this->mockArgs['mockUID'];
            if ( !$this->createUsersDataFile($uid) ) {
                
                throw new InvalidArgumentException(sprintf('Invalid data provided to %s; could find file data/%s.',
                    __METHOD__, 
                    $this->configs['data_flat_files']['orders']
                    ));
                
            }

        } catch( InvalidArgumentException $e ) {
            $message    = $datetime . "Error message: ";
            $message   .= $e->getMessage() ."\n";
            $data['errors'] = [
                'code'      => $e->getCode(),
                'message'   => $message
            ];
            error_log( $message."\n", 3, $this->logfile );
            return $error;
        }
         
        // set the timestamp if not already set
        if ( in_array("timestamp", $this->formFieldKeys) || empty($data['timestamp']) ) {
            $data['timestamp'] = $timestamp;
            
        }
        
        // log request to data/mobileuser file
        $this->data[$uid] = $data;
        $this->persistData();
        
        // return Entity object with object properties and values.
        return $this->createEntity($data);
    }
    
    
    /**
     * @param string $id, the client ID
     * @param int (optionally)$orderId, Dispatch or Route Guide Order ID
     * @return Object Entity, set from request method call.
     */
    public function fetch( $id, $orderId=NULL )
    {
        if (!$id) throw new InvalidArgumentException(sprintf(
            'Invalid data provided to %s; must be stdClass or Traversable',
            __METHOD__
            ));
        
        return $this->createEntity($this->data[$id]);
    }
    
    
    /**
     * @return Collection
     */
    public function fetchAll()
    {
        return new Collection($this->createCollection());
    }
    
    
    /**
     * @param string $id
     * @param array|Traversable|\stdClass $data
     * @param String $tempId, calls update once AccessToken is granted.
     * * default value for tempId is ''
     * 
     * @return Object Entity
     */
    public function update($id, $data, $tempId=null)
    {   
        // fail fast
        if (! is_object($data)) throw new InvalidArgumentException(
            sprintf('Invalid Data param for %s; must be stdClass or Traversable', __METHOD__)
        );
        if (! array_key_exists($id, $this->data)) throw new DomainException(
            sprintf('Data update failed; array key %s doesnt exist.', $id)
        );
        $data = (array) $data;
        
        if ( $id && is_array($data) ) {
            $updated = ArrayUtils::merge($this->data[$id], $data);
            $updated['timestamp'] = time();
        
            $this->data[$id] = $updated;
            $this->persistData();
        
            return $this->createEntity($updated);
        } else {
            $data['errors'] = [
                'code' => "406",
                'message' => __METHOD__.": failed to update Data with $id for flat file storage.",
            ];
            return $data;
        }
    }
    
    
    /**
     * @param string $id
     * @return bool
     */
    public function delete($id)
    {
        /*
        if (! Uuid::isValid($id)) {
            throw new DomainException('Invalid identifier provided', 404);
        }
        */
        
        if (! array_key_exists($id, $this->data)) {
            throw new DomainException('Cannot delete; no such status message', 404);
        }
        
        unset($this->data[$id]);
        $this->persistData();
        
        return true;
    }
    
    
    /**
     * @param String $className, class to instantiate, can be Entity or Collection
     * @return void
     */
    private function setClassObject($className)
    {
        $this->entityPrototype = new \ReflectionClass($className);
    }
    
    
    /**
     * @param array $item
     * @return Object Entity
     */
    protected function createEntity(array $item)
    {
        return $this->hydrator->hydrate($item, $this->entityPrototype);
    }
    
    
    /**
     * @return HydratingArrayPaginator
     */
    protected function createCollection()
    {
        return new HydratingArrayPaginator($this->data, $this->hydrator, $this->entityPrototype);
    }
    
    /**
     * Write mapped data to this->data object
     * This object has a reference from both here and ReceivedResource.
     */
    protected function persistData()
    {
        $this->configResource->overWrite($this->data);   
    }
    
    
    /**
     * open or create users file
     * @return boolean, true is file exists and is writable.
     */
    private function createUsersDataFile($uid=null)
    {

        $fileExists = false;
        $fname = $this->configs['data_flat_files']['orders'].".php";
        $file = __DIR__ . "/../tmp/$fname";
        
        if( file_exists($file) ) { $fileExists = true; } 
        else { // try and create it.
            $handle = fopen($file, 'w');
            $data = 'The users data.';
            fwrite($handle, $data);
            fclose($handle);
            if (file_exists($file) ) { $fileExists = true; }
        }
        return $fileExists;
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
        
        error_log($str."\n", 3, $this->logfile); 
    }
}
