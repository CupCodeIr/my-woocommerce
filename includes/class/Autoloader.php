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
        spl_autoload_register(function ($file_slug) : bool {

            $file_full_name = explode('\\',$file_slug);
            if($file_full_name[0] !== 'CupCode' || $file_full_name[1] !== 'MyWooCommerce') return false;
            $corrected_file_name = end($file_full_name);
            $base_path = dirname(__FILE__) . DIRECTORY_SEPARATOR;
            $file_base_paths[] =  '';
            $file_base_paths[] =  'trait' . DIRECTORY_SEPARATOR ;
            foreach ($file_base_paths as $file_base_path){
                $file_path = $base_path . $file_base_path  . $corrected_file_name . '.php';
                if(file_exists($file_path)){
                    return require_once $file_path;
                }


            }
            return false;


        });
    }

}

Autoloader::register();