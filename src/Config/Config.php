<?php

namespace Markoftw\fcpicks\Config;

$GLOBALS['config'] = array(
    'mysql' => array(
        'host' => 'localhost',
        'username' => 'root',
        'password' => 'USERNAME',
        'db' => 'PASSWORD'
    ),
    'mailer' => array(
        'enabled' => FALSE,
        'username' => 'USERNAME@gmail.com',
        'password' => 'PASSWORD'
    ),
);

class Config {

    public static function get($path = null) {
        if ($path) {
            $config = $GLOBALS['config'];
            $path = explode('/', $path);

            foreach ($path as $data) {
                if (isset($config[$data])) {
                    $config = $config[$data];
                }
            }

            return !is_array($config) ? $config : false;
        }

        return false;
    }

}
