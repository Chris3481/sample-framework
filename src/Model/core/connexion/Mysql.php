<?php

namespace App\Model\core\connexion;

use App\Model\core\AbstractModel;

class Mysql extends AbstractModel{

    protected $_connexion;

    public function __construct()
    {
        $host = \App::getConfig('db/mysql/host');
        $user = \App::getConfig('db/mysql/user');
        $password = \App::getConfig('db/mysql/password');
        $database = \App::getConfig('db/mysql/database');
        $port = \App::getConfig('db/mysql/port', '3306');

        try {
            $this->_connexion = new \PDO("mysql:host=$host;port=$port;dbname=$database", $user, $password);
            $this->_connexion->setAttribute(\PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES 'UTF8'");
            $this->_connexion->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $pE) {
            throw new \Exception('Error while connection to mysql database');
        }
    }

    /**
     * @return \PDO
     */
    public function getConnexion()
    {
        return $this->_connexion;
    }
}