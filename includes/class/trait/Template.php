<?php


namespace CupCode\MyWooCommerce;

defined('ABSPATH') or die('No script kiddies please!');


trait Template
{

    /**
     * Get template file which will outputs HTML Content
     * @param string $file_name template file name to load
     * @param bool $try_theme if true, considers theme files
     * @param array $args if needed, any arguments to load inside included page.
     * @return false|mixed
     */
    public function get_template(string $file_name, array $args = [], bool $try_theme = false): bool
    {

        $base_paths = [];
        if ($try_theme) {
            $base_paths[] = get_template_directory() . DIRECTORY_SEPARATOR . CC_MYWC_PLUGIN_BASE;
        }
        $base_paths[] = CC_MYWC_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'template';
        foreach ($base_paths as $base_path) {
            $file_path = $base_path . DIRECTORY_SEPARATOR . $file_name . '.php';
            if (file_exists($file_path)) {
                extract($args);
                return include $file_path;
            }
        }
        return false;


    }

}