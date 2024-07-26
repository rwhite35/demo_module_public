<?php
namespace Acme;


interface MapperInterface
{
    /**
     * @param array|\Traversable|\stdClass $data
     * @param string (optional)$user_name the user allowed to login.
     * @return Object Entity
     */
    public function create($data, $userName = null);
    
    /**
     * @param string $id, the client users XID
     * @param int (optional)$orderId the Route Guide order id
     * @return Object Entity
     */
    public function fetch($id, $orderId = null);
    
    /**
     * @return Object Collection
     */
    public function fetchAll();
    
    /**
     * @param string $id
     * @param array|\Traversable|\stdClass $data
     * @return Object Entity
     */
    public function update($id, $data);
    
    /**
     * @param string $id
     * @return Bool
     */
    public function delete($id);
}