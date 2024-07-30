<?php
namespace Acme;

/**
 * Paginated API transaction data (by Acme Access Token)
 * Uses specialized Laminas\Paginator\Adapter\ArrayAdapter 
 * instance for returning request input.
 *
 * Take the transaction data and hydrated an array.
 * the prototype fields are defined Entity object and data/acmelib.php 
 * 
 * Entity defines what that endpoint expects as input.
 */

use stdClass;
use Laminas\Paginator\Adapter\ArrayAdapter as ArrayPaginator;
use Laminas\Hydrator\HydratorInterface;

class HydratingArrayPaginator extends ArrayPaginator
{
    /**
     * @var object
     */
    protected $entityPrototype;
    
    /**
     * @var HydratorInterface
     */
    protected $hydrator;
    
    /**
     * @param array $array
     * @param null|HydratorInterface $hydrator
     * @param null|mixed $entityPrototype A prototype entity to use with the hydrator
     */
    public function __construct(array $array = array(), HydratorInterface $hydrator = null, $entityPrototype = null)
    {
        parent::__construct($array);
        $this->hydrator = $hydrator;
        $this->entityPrototype = $entityPrototype ?: new stdClass;
    }
    
    /**
     * Override getItems()
     *
     * Overrides getItems() to return a collection of entities based on the
     * provided Hydrator and entity prototype that is defined in data/demolib.php.
     *
     * @param int $offset
     * @param int $itemCountPerPage
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $set = parent::getItems($offset, $itemCountPerPage);
        if (! $this->hydrator instanceof HydratorInterface) {
            return $set;
        }
        
        $collection = array();
        foreach ($set as $item) {
            $collection[] = $this->hydrator->hydrate($item, clone $this->entityPrototype);
        }
        return $collection;
    }
}