<?php
namespace Unframed\Core;

/**
 * 
 */
class Environment
{

    /**
     * 
     * @return string
     */
    public static function getHost()
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
        } else {
            $host = $_SERVER['SERVER_NAME'];
        }

        if (preg_match('/^(\[[a-fA-F0-9:.]+\])(:\d+)?\z/', $host, $matches)) {
            $host = $matches[1];
        } else {
            $pos = strpos($host, ':');
            if ($pos !== false) {
                $host = strstr($host, ':', true);
            }
        }
        return $host;
    }

}
