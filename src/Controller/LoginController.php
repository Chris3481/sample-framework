<?php

namespace Controller;

class LoginController extends \Controller\AbstractController
{

    public function __construct()
    {
        $this->setLoginRequired(false);
    }

    public function indexAction()
    {
        $this->setLoginRequired(false);
        $this->setTitle('Login');

        $this->_loadLayout();

        return $this->renderLayout();
    }

    public function logoutAction()
    {
        /** @var \Model\security\Login $model */
        $model = \App::getModel('security/Login');
        $model->logout();

        $this->_redirect('index/login');
    }

    public function loginPostAction()
    {
        if($data = $this->getRequest()->getParams()) {
            /** @var \Model\security\Login $model */
            $model = \App::getModel('security/Login');
            if(isset($data['username']) && isset($data['password'])) {
                $model->login($data['username'], $data['password']);

            } else {
                echo 'username or password is empty';
            }

            if($model->isLoggedIn()) {
                $this->_redirect('index/index');
            }

            $this->_redirect('login');
        }
    }
}