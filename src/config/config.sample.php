<?php

return array(
    'general' => array(
        'title' => 'Neural entropy',
        'auth_required' => true,
        'logDir' => 'logs'
    ),
    'path' => array(
        'layout' => 'app/view/layout/default.xml',
        'template' => 'app/view/template',
        'skin' => 'skin',
    ),
    'defaultRoutes' => array(
        'controller' => 'index',
        'action' => 'index'
    ),
    'db' => array(
        'adapter' => 'Mysql',
        'host' => 'localhost',
        'user' => '*****',
        'password' => '*****',
        'database' => 'neural_entropy'
    )
);