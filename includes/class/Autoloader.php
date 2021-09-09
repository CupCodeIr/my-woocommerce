<?php

namespace CupCode\MyWooCommerce;

defined('ABSPATH') or die('No script kiddies please!');


class Autoloader
{
    public static function register()
    {
        spl_autoload_register(function ($class) {
            $correctedClassName = explode('\\',$class);
            $file = dirname(__FILE__) . DIRECTORY_SEPARATOR . (isset($correctedClassName[1]) ? $correctedClassName[1] : "notFound").'.php';
            if (file_exists($file)) {
                require_once $file;
                return true;
            }
            return false;
        });
    }

}

Autoloader::register();