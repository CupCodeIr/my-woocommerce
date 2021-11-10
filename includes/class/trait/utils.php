<?php


namespace CupCode\MyWooCommerce;

defined('ABSPATH') or die('No script kiddies please!');

trait Utils
{
    /**
     * @since 0.1.0
     * @return string
     */
    public static function get_local_language_code() : string
    {
        return (explode('_',get_locale()))[0];
    }
}