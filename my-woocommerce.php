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

use CupCode\MyWooCommerce\MyWooCommerce;

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
    require_once CC_MYWC_PLUGIN_PATH . '/includes/class/Autoloader.php';

}


MyWooCommerce::get_instance();








