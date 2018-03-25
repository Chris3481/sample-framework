<?php

namespace App\Model\security;

use App\Model\core\AbstractModel;

class Auth extends AbstractModel
{

    protected $_sessionName = 'admin';


    public function __construct()
    {
        @session_start();
    }

    public function login($username, $password)
    {
        $query = 'select user_id, first_name, last_name, username from users where username = :username and password = sha1(:password) limit 1';
        try {
            $query = $this->getConnexion()->prepare($query, array(\PDO::ATTR_CURSOR, \PDO::CURSOR_SCROLL));
            $query->bindValue('username', $username);
            $query->bindValue('password', $password);
            $query->execute();
            $data = $query->fetch(\PDO::FETCH_ASSOC);

            if($data['user_id'] > 0){
                $this->setSession($data);
                return $data;
            } else {
                return false;
            }
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function logout()
    {
        $sessionName = $this->_sessionName;
        unset($_SESSION[$sessionName]);
        $_SESSION[$sessionName] = false;

        return $this;
    }

    public function setSession($data)
    {
        $sessionName = $this->_sessionName;
        $_SESSION[$sessionName] = $data;

        return $this;
    }

    public function getSession()
    {
        $sessionName = $this->_sessionName;

        return isset($_SESSION[$sessionName]) ? $_SESSION[$sessionName] : NULL;
    }

    public function isLoggedIn()
    {
        $sessionName = $this->_sessionName;
        if(isset($_SESSION[$sessionName]) && $_SESSION[$sessionName] != '') {
            return true;
        } else {
            return false;
        }
    }

}