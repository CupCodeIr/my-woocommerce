<?php

namespace CupCode\MyWooCommerce;

defined('ABSPATH') or die('No script kiddies please!');


class Autoloader
{
    /**
     * @since 0.1.0
     */
    public static function register()
    {
        spl_autoload_register(function ($class) {

            $class_full_name = explode('\\',$class);
            $correctedClassName = end($class_full_name);
            $file = dirname(__FILE__) . DIRECTORY_SEPARATOR . $correctedClassName . '.php';
            if (file_exists($file )) {
                require_once $file;
                return true;
            }
            return false;
        });
    }

}

Autoloader::register();