<?php

namespace App\Controller;

use App;

class AbstractController
{

    protected $_request = null;

    public function __construct()
    {
        $required = App::getConfig('general/login_required');

        $this->setLoginRequired($required);
    }

    public function renderLayout()
    {
        $layout = $this->getLayout();

        try {
            if ($root = $layout->getBlock('root')) {
                $html = $root->toHtml();
            } else {
                throw new \Exception("Root block is empty");
            }

        } catch (Exeption $e) {
            $html = '';
            echo $e->getMessage();
        }

        return $html;
    }

    /**
     * @return App\Model\core\Layout
     */
    public function getLayout()
    {
        return App::getLayout();
    }

    /**
     * @param App\Model\core\Request $request
     * @return App\Controller\AbstractController
     */
    public function setRequest($request)
    {
        $this->_request = $request;
        return $this;
    }

    /**
     * @return App\Model\core\Request
     */
    public function getRequest()
    {
        return $this->_request;
    }

    public function setLoginRequired($enable = true)
    {
        /** @var App\Model\security\Auth $model */
        $model = new App\Model\security\Auth();

        if (!$model->isLoggedIn() && $enable) {
            $this->_redirect('login');
            exit;
        }

    }

    protected function _redirect($url)
    {
        header('Location: /' . $url);
        exit;
    }

    protected function _loadLayout()
    {
        $this->getLayout()->loadLayout();
    }

    /**
     * @return array
     */
    public function getSorters()
    {
        $sort = array();
        if (!isset($_POST['order']) || $_POST['order'] == "") return false;

        $sortIndex = $_POST['order'][0]['column'];
        $sort['column'] = $_POST['columns'][$sortIndex]['data'];
        $sort['dir'] = $_POST['order'][0]['dir'];
        return $sort;
    }

    /*
     * @param array $data
     */
    public function sendResponse($json)
    {
        $_resquest = $this->getRequest();
        if ($callback = $_resquest->getParam('callback')) {
            echo $callback . '(' . json_encode($json) . ');';
        } else {
            echo json_encode($json);
        }
    }

    /*
     * @param array $json
     */
    public function sendGridResponse($data, $total, $code = 0)
    {
        $_resquest = $this->getRequest();
        $response = array('code' => $code, 'data' => $data, 'recordsTotal' => $total, 'recordsFiltered' => $total, 'draw' => $_resquest->getParam('draw'));
        echo json_encode($response);
    }

    public function setTitle($title)
    {
        if ($header = $this->getLayout()->getBlock('root')) {
            $header->setTitle($title);
        }

        return $this;
    }

}