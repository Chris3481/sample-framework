<?php

use App\Model\core\Exception as ExeptionHandler;
use App\Model\core\Request;
use App\Model\core\Layout;
use App\Controller\AbstractController;
use Predis\Client;

class App
{
    static private $_models;
    static private $_layout;
    static private $_config;
    static private $_dbAdapter;
    static private $_redis;

    /**
     * @return AbstractController
     */
    public static function getController()
    {
        /** @var Request $_request */
        $_request = new Request();

        if (!$controller = $_request->getParam('controller')) {
            $controller = $_request->getDefaultController();
        }
        $controllerNamespace = 'Controller\\' . ucfirst($controller) . 'Controller';
        $_controller = new $controllerNamespace();
        $_controller->setRequest($_request);

        return $_controller;
    }

    public static function getAction()
    {
        /** @var Request $_request */
        $_request = new Request();

        if (!$action = $_request->getParam('action')) {
            $action = $_request->getDefaultAction();
        }
        $action = $action . 'Action';
        return $action;
    }


    public static function getModel($namespace, $params = null)
    {
        $model = null;
        $namespaceArr = explode('/', $namespace);
        $keys = array_keys($namespaceArr);
        $key = end($keys);
        $namespaceArr[$key] = ucfirst($namespaceArr[$key]);

        $namespace = '\\Model\\' . implode('\\', $namespaceArr);
        try {
            $model = new $namespace($params);
        } catch (Exception $e) {
            throw new Exception ("Model $namespace does not exist");
        }

        return $model;
    }

    public static function getSingleton($namespace, $params = null)
    {
        if (!isset(self::$_models[$namespace])) {
            try {
                $model = self::getModel($namespace, $params);
                self::$_models[$namespace] = $model;
            } catch (Exception $e) {
                throw new Exception ("Model $namespace does not exist");
            }
        }

        return self::$_models[$namespace];
    }

    public static function getLayout()
    {
        if(!self::$_layout) {
            self::$_layout = new Layout();
        }

        return self::$_layout;
    }

    public static function getAdapter($type = null)
    {
        if (!$type) {
            $type = self::getConfig('db/adapter', 'Mysql');
        }

        try {
            $adapter = self::getModel('core/connexion/' . $type);
            self::$_dbAdapter[$type] = $adapter;
        } catch (Exception $e) {
            throw new Exception("Database adapter $type does not exist");
        }

        return self::$_dbAdapter[$type];
    }

    /**
     * @return \Predis\Client
     */
    public static function getCache()
    {
        if (!isset(self::$_redis)) {
            $config = self::getConfig('db/redis');
            self::$_redis = new Client($config);
        }

        return self::$_redis;
    }

    public static function getConfig($path = null, $default = null)
    {
        if (!self::$_config) {
            self::$_config = include ROOT_PATH . '/config/config.php';
        }

        if ($path) {
            $pathArr = explode('/', $path);

            $data = $default;
            foreach ($pathArr as $key => $p) {

                if ($key == 0 && isset(self::$_config[$p]) && self::$_config[$p] != '') {
                    $data = self::$_config[$p];
                } elseif ($key > 0 && isset($data[$p]) && $data[$p] != '') {
                    $data = $data[$p];
                } else {
                    $data = $default;
                    break;
                }
            }
            return $data;

        } else {
            return self::$_config;
        }
    }

    public static function log($data)
    {
        file_put_contents(ROOT_PATH . '/var/error.log', print_r($data, true) . "\n", FILE_APPEND);
    }

    public static function getBaseUrl()
    {
        if (isset($_SERVER['HTTPS'])) {
            $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
        } else {
            $protocol = 'http';
        }
        return $protocol . "://" . $_SERVER['HTTP_HOST'];
    }

    public static function getBasePath()
    {
        return ROOT_PATH;
    }

    public static function load($namespace)
    {
        $namespace = str_replace('\\', '/', $namespace);
        $file = ROOT_PATH . "/app/$namespace.php";

        if (file_exists($file)) {
            include_once($file);
            return true;
        }

        return false;
    }

    public static function loadContent()
    {
        /** @var AbstractController $_controller */
        $_controller = self::getController();
        $action = self::getAction();

        return $_controller->$action();
    }

    public static function run($output = true)
    {
        require(ROOT_PATH . '/vendor/autoload.php');

        // Activate autoload
        spl_autoload_register(array('self', 'load'));

        set_exception_handler(array(ExeptionHandler::class, 'detection'));

        if ($output) echo self::loadContent();
    }
}