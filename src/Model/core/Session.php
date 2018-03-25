<?php

namespace App\Model\core;

class Session extends AbstractModel
{
    protected $_sessionName = 'general';


    public function __construct($sessionName = null)
    {
        @session_start();

        if($sessionName) {
            $this->_sessionName = $sessionName;
        }

         $this->_data = &$_SESSION[$this->_sessionName];
    }

}