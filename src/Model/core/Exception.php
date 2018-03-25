<?php
namespace App\Model\core;

class Exception
{

    public static function detection($e)
    {
        $message = 'File: ' . $e->getFile() . ' (' . $e->getLine() . ')' ."\n";
        $message .= 'Message: ' . $e->getMessage() ."\n";
        $message .= $e->getTraceAsString() ."\n" . "\n";

        \App::log($message);

        echo nl2br($message);
    }
}
