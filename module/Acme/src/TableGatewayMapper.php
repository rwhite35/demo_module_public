<?php
namespace Acme;
/**
 * Acme\TableGatewayMapper | Acme Utilities
 * Similar to Acme\ArrayMapper, it takes a DB result set and maps it to
 * an PHP associative array based on an Entity or Collection prototype.
 * 
 * This class implements MapperInterface and should conform to the 
 * standard REST verbs -- create, fetch, fetchAll, update etc...
 * 
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014-2018 Zend Technologies USA Inc. (http://www.zend.com)
 */

use DomainException;
use InvalidArgumentException;
use Traversable;
use Laminas\Paginator\Adapter\DbTableGateway;
use Laminas\Stdlib\ArrayUtils;
use Laminas\ApiTools\Admin\InputFilter\CreateContentNegotiationInputFilter;
use ZendBench\ServiceManager\FetchCachedServicesBench;

/**
 * Mapper implementation using a Laminas\Db\TableGateway
 */
class TableGatewayMapper implements MapperInterface
{
    /**
     * @var TableGateway
     */
    protected $table;

    /**
     * @param TableGateway $table
     */
    public function __construct(
        TableGateway $table
        
    ) {
        $this->table = $table;
        
    }

    
    /**
     * @method Create
     * inserts a record in to the table and field specified.
     * 
     * @param array|Traversable|\stdClass $data
     * @return Object Entity
     */
    public function create($data, $userName=null)
    {
        // fail fast
        if (! is_object($data)) throw new InvalidArgumentException(
            sprintf('Invalid Data param for %s; must be stdClass or Traversable', __METHOD__)
        );

        if ($data instanceof Traversable) {
            $data = ArrayUtils::iteratorToArray($data);
        }
        $data = (array) $data;

        if (! isset($data['timestamp'])) {
            $data['timestamp'] = time();
        }
        
        $this->table->insert($data);

        $resultSet = $this->table->select(['id' => $data['id']]);
        
        if (0 === count($resultSet)) {
            throw new DomainException('Insert operation failed or did not result in new row', 500);
        }
        return $resultSet->current();
    }

    
    /**
     * @method Fetch
     * fetches a record with a matching $id as primary key or
     * whatever the id column.
     * 
     * @param string $id, the primary key or id field
     * @param string $uid, the user id
     * @param string $table, the db adaptor table
     * @return Array $resultSet
     */
    public function fetch( $id, $orderId = null )
    {
        try {
            $where=[ "client_id" => $id ];
            
            if ( $this->table instanceof TableGateway ) {
                $resultSet = $this->table->select( $where );
            
            } else {
                throw new InvalidArgumentException(sprintf(
                    "Table not an instance of TableGateway.",
                    __METHOD__
                    ));  
            }
            
            if (0 === count($resultSet)) {
                throw new DomainException('No result set returned', 400);
            }
            
        } catch( InvalidArgumentException $e ) {
            $error = "Acme\TableGatewayMapper error:\n";
            $error .= $e->getMessage();
            error_log($error, 3, "/tmp/api.acme.log");
        }
        
        return $resultSet->current();
    }

    
    /**
     * @method FetchAll 
     * returns all records matching the query parameters.
     * 
     * If using HAL+Json, the collection should specify the 
     * pagingation and number of records to show per page.
     * 
     * @return Object Collection
     */
    public function fetchAll()
    {
        // return new Collection(new DbTableGateway($this->table, null, ['timestamp' => 'DESC']));
    }

    /**
     * @method Update
     * updates a record matching the id and data.
     * data is expected to be the same structure as the table columns.
     * 
     * @param string $id
     * @param array|Traversable|\stdClass $data
     * @return Object Entity
     */
    public function update($id, $data)
    {
        // in this case, $data param should be plain old array
        if (! is_object($data)) {
            $data = (array) $data;
        }

        if (! isset($data['timestamp'])) {
            $data['timestamp'] = time();
        }

        $this->table->update($data, ['id' => $id]);

        $resultSet = $this->table->select(['id' => $id]);
        if (0 === count($resultSet)) {
            throw new DomainException('Update operation failed or result in row deletion', 500);
        }
        return $resultSet->current();
    }

    /**
     * @param string $id
     * @return bool
     */
    public function delete($id)
    {
        $result = $this->table->delete(['id' => $id]);
        if (! $result) { return false; }
        return true;
    }
}
