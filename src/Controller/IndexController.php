<?php

namespace App\Controller;

class IndexController extends AbstractController
{

    public function indexAction()
    {
        $this->setTitle('Sample');

        $this->_loadLayout();

        return $this->renderLayout();
    }
    
}