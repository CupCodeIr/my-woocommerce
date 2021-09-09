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
    require_once CC_MYWC_PLUGIN_PATH . '/includes/class/Autoloader.php';

}


\CupCode\MyWooCommerce\Attribute::get_instance(CC_MYWC_PLUGIN_SLUG);


/**
 * Enqueue styles and scripts for non-admin environment
 */
add_action('wp_enqueue_scripts', function () {


});

/**
 * Enqueue styles and scripts for admin environment
 */
add_action('admin_enqueue_scripts', function ($hook) {

    global $post_type;
    if(is_admin() && $hook === 'post-new.php' && $post_type ===  CC_MYWC_PLUGIN_SLUG . '_sa'){
        $locale = (explode('_',get_locale()))[0];
        wp_enqueue_style(CC_MYWC_PLUGIN_SLUG . '-select2', CC_MYWC_PLUGIN_URL . 'assets/css/select2.min.css', array(), false, false);
        wp_enqueue_script(CC_MYWC_PLUGIN_SLUG . '-select2', CC_MYWC_PLUGIN_URL . 'assets/js/select2/select2.min.js', [], false, true);
        wp_localize_script(CC_MYWC_PLUGIN_SLUG . '-select2',  'select2_vars', [
            'plugin_slug' => CC_MYWC_PLUGIN_SLUG,
            'is_rtl' => is_rtl() ? 'true' : 'false',
            'language' => $locale
        ]);
        wp_enqueue_script(CC_MYWC_PLUGIN_SLUG . '-select2-lang', CC_MYWC_PLUGIN_URL . 'assets/js/select2/i18n/' . $locale . '.js', [], false, true);
        wp_enqueue_script(CC_MYWC_PLUGIN_SLUG . '-attributes-edit-post', CC_MYWC_PLUGIN_URL . 'assets/js/admin/attributes-edit-post.js', [], false, true);

    }
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