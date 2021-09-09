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
    if ($cc_mywc_should_wipe['dc_attributes']) {
        //TODO
//        cc_mywc_wipe_selectable_attributes();
    }

}

/**
 * Wipes all posts and related data such as postmeta and related taxonomy of '_sa' post type
 */
function cc_mywc_wipe_selectable_attributes()
{

    global $wpdb;
    $wpdb->query(
        "DELETE p,pm,tr FROM {$wpdb->posts} p
JOIN {$wpdb->postmeta} pm ON {$wpdb->posts}.id = {$wpdb->postmeta}.post_id
JOIN {$wpdb->term_relationships} tr ON {$wpdb->posts}.id = {$wpdb->term_relationships}.object_id
WHERE {$wpdb->posts}.post_type = '" . CC_MYWC_PLUGIN_SLUG . "_sa'"
    );

    $update_taxonomies = ['product_tag', 'product_cat'];
    foreach ($update_taxonomies as $update_taxonomy) {
        $get_terms_args = array(
            'taxonomy' => $update_taxonomy,
            'fields' => 'ids',
            'hide_empty' => false,
        );

        $update_terms = get_terms($get_terms_args);
        wp_update_term_count_now($update_terms, $update_taxonomy);
    }

}

/**
 * Delete translation files
 */


function cc_mywc_remove_translation_files()
{

    $mo_files = glob(CC_MYWC_PLUGIN_PATH . '/languages/*.mo');
    foreach ($mo_files as $mo_file)
        unlink(trailingslashit(WP_CONTENT_DIR) . '/languages/plugins/' . wp_basename($mo_file));


}

cc_mywc_remove_translation_files();

