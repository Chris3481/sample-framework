<?php

namespace App\Model\core\connexion;

use App;
use App\Model\core\AbstractModel;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

class Mysql extends AbstractModel
{

    protected $_connexion;


    public function __construct()
    {
        $entitiesPath = array(
            ROOT_PATH . '/app/Model'
        );

        $isDevMode = true;
        $proxyDir = null;
        $cache = null;
        $useSimpleAnnotationReader = false;

        $dbParams = App::getConfig('db/mysql');

        $config = Setup::createAnnotationMetadataConfiguration($entitiesPath, $isDevMode, $proxyDir, $cache, $useSimpleAnnotationReader);

        try {
            $this->_connexion = EntityManager::create($dbParams, $config);

        } catch(ORMException $e) {
            // @todo catch database connexion exception
        }
    }

    /**
     * @return EntityManager
     */
    public function getConnexion()
    {
        return $this->_connexion;
    }
}