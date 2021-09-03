<?php
/**
 * Plugin Name: My WooCommerce
 * Plugin URI: https://cupcode.xyz/en/portfolio/my-woocommerce
 * Description: A WooCommerce plugin which let your customers rapidly filter and view products with attributes they chose.
 * Version: 0.1.0
 * Author: Artin
 * Author URI: https://cupcode.xyz/en
 * Text Domain: cupcode-mywc
 * License: GPL2
 * Requires at least: 5.2
 * Requires PHP: 7.0
 */

defined('ABSPATH') or die('No script kiddies please!');
define('CC_MYWC_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CC_MYWC_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('CC_MYWC_PLUGIN_BASE', plugin_basename(__DIR__));
define('CC_MYWC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CC_MYWC_PLUGIN_SLUG', 'cupcode_mywc');
define('CC_MYWC_PLUGIN_VERSION', '0.1.0');

/**
 * Check for dependencies
 **/
if (
!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))
) return;

/**
 * Load Redux framework
 **/
if (!class_exists('ReduxFramework') && file_exists(CC_MYWC_PLUGIN_PATH . '/settings/redux-core/framework.php'))
    require_once CC_MYWC_PLUGIN_PATH . '/settings/redux-core/framework.php';

/**
 * Include plugin files if Redux is loaded
 **/
if (class_exists('ReduxFramework')) {
    require_once CC_MYWC_PLUGIN_PATH . '/settings/init.php';
}


/**
 * Enqueue styles and scripts for non-admin environment
 */
add_action('wp_enqueue_scripts', function () {


});

/**
 * Do some stuffs on plugin activation
 */
register_activation_hook(CC_MYWC_PLUGIN_BASENAME, function () {

    cc_mywc_copy_translations();
    cc_mywc_handle_plugin_version();


});


/**
 * A function to copy plugin translations to Wordpress languages directory
 */
function cc_mywc_copy_translations(){

    $mo_files = glob(CC_MYWC_PLUGIN_PATH . '/languages/*.mo');
    foreach ($mo_files as $mo_file)
        copy($mo_file, trailingslashit(WP_CONTENT_DIR) . '/languages/plugins/' . wp_basename($mo_file));

}


/**
 * Updates plugin version in wp_options
 */
function cc_mywc_handle_plugin_version(){

    update_option(CC_MYWC_PLUGIN_SLUG . '_version',CC_MYWC_PLUGIN_VERSION);

}