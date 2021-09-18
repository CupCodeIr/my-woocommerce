<?php

namespace CupCode\MyWooCommerce;

defined('WP_UNINSTALL_PLUGIN') or die('No script kiddies please!');

define('CC_MYWC_PLUGIN_SLUG', 'cupcode_mywc');
define('CC_MYWC_PLUGIN_PATH', plugin_dir_path(__FILE__));


$should_wipe = get_option(CC_MYWC_PLUGIN_SLUG . '_settings');

/**
 * If user selected wipe, so wipe!
 */
if ($should_wipe['dc_all']) {

    delete_option(CC_MYWC_PLUGIN_SLUG . '_settings');
    delete_option(CC_MYWC_PLUGIN_SLUG . '_attribute_endpoint');
    wipe_selectable_attributes();
    wipe_user_attributes();

} else {
    if ($should_wipe['dc_settings']){

        delete_option(CC_MYWC_PLUGIN_SLUG . '_settings');
        delete_option(CC_MYWC_PLUGIN_SLUG . '_attribute_endpoint');

    }
    if ($should_wipe['dc_attributes']) {

        wipe_selectable_attributes();
        wipe_user_attributes();
    }

}

/**
 * Wipes all posts and related data such as postmeta and related taxonomy of '_sa' post type
 * @since 0.1.0
 */
function wipe_selectable_attributes()
{

    global $wpdb;
    $wpdb->query("DELETE p,pm
FROM {$wpdb->posts} p

         LEFT JOIN {$wpdb->postmeta} pm
                   ON (p.ID = pm.post_id)
WHERE p.post_type = " . CC_MYWC_PLUGIN_SLUG . "'_sa'");


}

/**
 * Wipes all posts and related data such as postmeta and related taxonomy of '_ua' post type
 * @since 0.1.0
 */
function wipe_user_attributes()
{

    global $wpdb;
    $wpdb->query("DELETE p,pm
FROM {$wpdb->posts} p

         LEFT JOIN {$wpdb->postmeta} pm
                   ON (p.ID = pm.post_id)
WHERE p.post_type = " . CC_MYWC_PLUGIN_SLUG . "'_ua'");


}

/**
 * Delete translation files
 * @since 0.1.0
 */


function remove_translation_files()
{

    $mo_files = glob(CC_MYWC_PLUGIN_PATH . '/languages/*.mo');
    foreach ($mo_files as $mo_file)
        unlink(trailingslashit(WP_CONTENT_DIR) . '/languages/plugins/' . wp_basename($mo_file));


}

remove_translation_files();

