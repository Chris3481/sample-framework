<?php

namespace App\Model\core\connexion;


use App\Model\core\AbstractModel;

class MongoDb extends AbstractModel
{

    protected $_connexion = NULL;
    protected $_collection = null;
    protected $_options = array();
    protected $_filters = array();

    protected $_bulkWrite = null;
    protected $_update = null;


    public function __construct()
    {
        try {
            $m = new \MongoDB\Driver\Manager('mongodb://localhost:27017');
            $this->_connexion = $m;
        } catch (\Exception $e) {
            echo "Couldn't connect to mongodb, is the mongo process running?";
            exit;
        }
    }

    public function setCollection($collection)
    {
        $db = \App::getConfig('db/mongodb/database');
        $this->_collection = sprintf('%s.%s', $db, $collection);
        return $this;
    }

    /**
     * @return \MongoDB\Driver\Manager
     */
    public function getConnexion()
    {
        return $this->_connexion;
    }

    /**
     * @return \MongoDB\Driver\Query
     */
    public function getQuery()
    {
        return new \MongoDB\Driver\Query($this->_filters, $this->_options);
    }

    /**
     * fileld => value
     * @param array $filter
     * @return \Model\core\connexion\MongoDb
     */
    public function addFilter($field, $cond)
    {
        $this->_filters[$field] = $cond;
        return $this;
    }

    /**
     * fileld => direction
     * @param array $params
     * @return \Model\core\connexion\MongoDb
     */
    public function setSortBy($params)
    {
        $this->_options['sort'] = $params;
        return $this;
    }

    /**
     * @param $limit
     * @return \Model\core\connexion\MongoDb
     */
    public function setLimit($limit)
    {
        $this->_options['limit'] = $limit;
        return $this;
    }

    /**
     * @param $offset
     * @return \Model\core\connexion\MongoDb
     */
    public function setOffset($offset)
    {
        $this->_options['skip'] = $offset;
        return $this;
    }

    public function addFieldsToSelect($fields)
    {
        $projection = array();
        foreach ($fields as $field) {
            $projection[$field] = 1;
        }

        $this->_options['projection'] = $projection;

        return $this;
    }

    public function count()
    {
        $arr = explode('.', $this->_collection);

        $cmd = array("count" => $arr[1]);
        if($this->_filters) {
            $filters = array('query' => $this->_filters);
            $cmd = array_merge($cmd, $filters);
        }

        $count = new \MongoDB\Driver\Command($cmd);
        $res = $this->getConnexion()->executeCommand($arr[0], $count);
        $data = $res->toArray();

        $this->_filters = array();

        return isset($data[0]->n) ? $data[0]->n : null;
    }

    /**
     * @return \MongoDB\Driver\BulkWrite
     */
    public function getWrite()
    {
        if (!$this->_bulkWrite) {
            $this->_bulkWrite = new \MongoDB\Driver\BulkWrite;
        }

        return $this->_bulkWrite;
    }

    /**
     * @param $collection
     * @return \MongoDB\Driver\Cursor
     */
    public function executeQuery()
    {
        $readPreference = new \MongoDB\Driver\ReadPreference(\MongoDB\Driver\ReadPreference::RP_PRIMARY);
        $res = $this->getConnexion()->executeQuery($this->_collection, $this->getQuery(), $readPreference);
        $this->_filters = array();
        $this->_options = array();

        return $res;
    }

    /**
     * @param $collection
     * @return \MongoDB\Driver\WriteResult
     */
    public function executeWrite()
    {
        $res = null;
        if(count($this->getWrite()) > 0) {
            $res = $this->getConnexion()->executeBulkWrite($this->_collection, $this->getWrite());
            $this->_bulkWrite = null;
        }

        return $res;
    }

    public function executeUpdate($new, $options = array('multi' => true, 'upsert' => false))
    {
        $newObj = ['$set' => $new];
        $this->getWrite()->update($this->_filters, $newObj, $options);
        $res = $this->getConnexion()->executeBulkWrite($this->_collection, $this->getWrite());
        $this->_filters = array();

        return $res;
    }

    public function executeDelete()
    {
        $this->getWrite()->delete($this->_filters);
        $res = $this->getConnexion()->executeBulkWrite($this->_collection, $this->getWrite());
        $this->_bulkWrite = null;
        $this->_filters = array();

        return $res;
    }

}