<?php

namespace App\Model\core;

class Request extends AbstractModel
{

    protected $_request;

    public function getRequest()
    {
        return $this->_request;
    }

    public function getParam($param, $default=null)
    {
        $data = $default;
        $paramArr = explode('/', $param);

        foreach($paramArr as $key=>$p) {

            if($key == 0) {
                if(isset($_POST[$p]) && $_POST[$p] != '') {
                    $data = $_POST[$p];
                } else if(isset($_GET[$p]) && $_GET[$p] != '') {
                    $data = $_GET[$p];
                }
            } elseif($key > 0 && isset($data[$p]) && $data[$p] != '') {
                $data = $data[$p];
            } else {
                $data = $default;
                break;
            }
        }
        return $data;
    }

    public function getParams($default=null)
    {
        if(isset($_POST) && count($_POST) > 0) {
            return $_POST;
        } elseif(isset($_POST) && count($_POST) > 0) {
            return $_GET;
        } else {
            return $default;
        }
    }

    public function getDefaultController()
    {
        $config = $this->getConfig();
        return $config['defaultRoutes']['controller'];
    }

    public function getDefaultAction()
    {
        $config = $this->getConfig();
        return $config['defaultRoutes']['action'];
    }
}