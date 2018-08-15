<?php

namespace App;

use App;
use App\Model\core\Exception as ExeptionHandler;

class SampleFramework
{
    private $directory;
    private $prefix;
    private $prefixLength;

    /**
     * @param string $baseDirectory Base directory where the source files are located.
     */
    public function __construct($baseDirectory = __DIR__)
    {
        include $baseDirectory .'/App.php';

        $this->directory = $baseDirectory;
        $this->prefix = __NAMESPACE__.'\\';
        $this->prefixLength = strlen($this->prefix);

        // Activate autoload
        spl_autoload_register(array(App::class, 'load'));

        set_exception_handler(array(ExeptionHandler::class, 'detection'));
    }

    public function run()
    {
        echo App::run();
    }

    public function runCommands()
    {
        App::runCommands();
    }
}
