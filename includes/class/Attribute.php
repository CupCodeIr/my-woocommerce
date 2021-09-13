<?php

namespace CupCode\MyWooCommerce;

defined('ABSPATH') or die('No script kiddies please!');


abstract class Attribute
{
    use Utils,Template;

    protected static $admin_cap = "manage_options";

    protected $wpdb;

    protected function __construct()
    {
    }

    abstract public static function get_instance(): Attribute;

    protected function init(){
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    abstract public function register_attribute_post_type(): bool;

    abstract public function save_attributes(int $post_id) : bool;

    /**
     * Gets all used taxonomies such as product categories and product tags
     * @return array
     * @since 0.1.0
     */
    protected function get_used_taxonomies() : array
    {

        return $this->wpdb->get_col("select meta_value from {$this->wpdb->postmeta} where meta_key = '_" . CC_MYWC_PLUGIN_SLUG ."_category' or meta_key = '_" . CC_MYWC_PLUGIN_SLUG."_tag'",3);
    }

    protected function get_post_attribute_meta($post_id,$type = 'all') : array
    {
        $all_meta = $this->wpdb->get_results("select meta_key, meta_value from {$this->wpdb->postmeta} where post_id = '{$post_id}' and (meta_key = '_" . CC_MYWC_PLUGIN_SLUG .  "_category' or meta_key = '_" . CC_MYWC_PLUGIN_SLUG .  "_tag' or meta_key = '_" . CC_MYWC_PLUGIN_SLUG .  "_attribute')");
        $meta_data['categories'] = [];
        $meta_data['tags'] = [];
        $meta_data['attributes'] = [];

        if($type === "all"){
            foreach ($all_meta as $meta){
                if($meta->meta_key === "_" . CC_MYWC_PLUGIN_SLUG .  "_category")
                    $meta_data['categories'][] = $meta->meta_value;
                elseif ($meta->meta_key === "_" . CC_MYWC_PLUGIN_SLUG .  "_tag")
                    $meta_data['tags'][] = $meta->meta_value;
                elseif ($meta->meta_key === "_" . CC_MYWC_PLUGIN_SLUG .  "_attribute")
                    $meta_data['attributes'][] = $meta->meta_value;
            }
        }
        return $meta_data;

    }







}