<?php
defined('WP_UNINSTALL_PLUGIN') or die('No script kiddies please!');

define('CC_MYWC_PLUGIN_SLUG', 'cupcode_mywc');
define('CC_MYWC_PLUGIN_PATH', plugin_dir_path(__FILE__));


$cc_mywc_should_wipe = get_option(CC_MYWC_PLUGIN_SLUG . '_settings');

/**
 * If user selected wipe, so wipe!
 */
if ($cc_mywc_should_wipe['dc_all']) {

    delete_option(CC_MYWC_PLUGIN_SLUG . '_settings');

} else {
    if ($cc_mywc_should_wipe['dc_settings'])
        delete_option(CC_MYWC_PLUGIN_SLUG . '_settings');
}

/**
 * Delete translation files
 */


function cc_mywc_remove_translation_files(){

    $mo_files = glob(CC_MYWC_PLUGIN_PATH . '/languages/*.mo');
    foreach ($mo_files as $mo_file)
        unlink(trailingslashit(WP_CONTENT_DIR) . '/languages/plugins/' . wp_basename($mo_file));


}
cc_mywc_remove_translation_files();

