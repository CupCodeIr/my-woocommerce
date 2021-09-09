<?php

namespace CupCode\MyWooCommerce;


use Redux;
use WP_Error;

class Attribute
{
    private $plugin_slug;
    private static $instance;


    public static function get_instance($plugin_slug): Attribute
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance->init($plugin_slug);
        }
        return self::$instance;
    }

    private final function init($plugin_slug)
    {
        $this->plugin_slug = $plugin_slug;
        add_action('init', function () {
            $this->register_selectable_attribute_post_type();
            $this->register_user_attributes_post_type();
        });
    }

    /**
     * Registers a post type for saving attributes which are selectable for users
     * @return bool
     * @since 0.1.0
     */
    public function register_selectable_attribute_post_type(): bool
    {

        $admin_cap = "manage_options";
        $labels = [
            /* translator: Selectable Attributes post type name */
            'name' => esc_html_x('Selectable Attributes', 'post-type', 'cupcode-mywc'),
            /* translator: Selectable Attributes post type singular name */
            'singular_name' => esc_html_x('Selectable Attribute', 'post-type', 'cupcode-mywc'),
            /* translator: Selectable Attributes post type menu name */
            'menu_name' => esc_html_x('Selectable Attributes', 'post-type', 'cupcode-mywc'),
            /* translator: Selectable Attributes post type admin bar name */
            'name_admin_bar' => esc_html_x('Selectable Attribute', 'post-type', 'cupcode-mywc'),
            'add_new' => esc_html_x('Add New', 'post-type', 'cupcode-mywc'),
            'add_new_item' => esc_html_x('Add New Selectable Attribute', 'post-type', 'cupcode-mywc'),
            'new_item' => esc_html_x('New Selectable Attribute', 'post-type', 'cupcode-mywc'),
            'edit_item' => esc_html_x('Edit Selectable Attribute', 'post-type', 'cupcode-mywc'),
            'view_item' => esc_html_x('View Selectable Attribute', 'post-type', 'cupcode-mywc'),
            'all_items' => esc_html_x('All Selectable Attributes', 'post-type', 'cupcode-mywc'),
            'search_items' => esc_html_x('Search Selectable Attributes', 'post-type', 'cupcode-mywc'),
            'not_found' => esc_html_x('No selectable attributes found.', 'post-type', 'cupcode-mywc'),
            'not_found_in_trash' => esc_html_x('No Selectable Attributes found in Trash.', 'post-type', 'cupcode-mywc'),
            'archives' => esc_html_x('Selectable Attribute archives', 'post-type', 'cupcode-mywc'),
            'filter_items_list' => esc_html_x('Filter selectable attribute list', 'post-type', 'cupcode-mywc'),
            'items_list_navigation' => esc_html_x('Selectable Attributes list navigation', 'post-type', 'cupcode-mywc'),
            'items_list' => esc_html_x('Selectable Attributes list', 'post-type', 'cupcode-mywc'),
        ];

        $args = [
            'labels' => $labels,
            'public' => true,
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => $this->plugin_slug . '_settings_options',
            'show_in_rest' => false,
            'query_var' => false,
            'rewrite' => false,
            'capabilities' => [
                'edit_post' => $admin_cap,
                'read_post' => $admin_cap,
                'delete_post' => $admin_cap,
                'edit_posts' => $admin_cap,
                'edit_others_posts' => $admin_cap,
                'publish_posts' => $admin_cap,
                'read_private_posts' => $admin_cap,
                'create_posts' => $admin_cap,
            ],
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => ['title', 'author'],
            'taxonomies' => ['product_tag', 'product_cat'],
            'delete_with_user' => false,
            'register_meta_box_cb' => [$this, 'register_selectable_attributes_meta_box']
        ];

        $post_type = register_post_type($this->plugin_slug . '_sa', $args);
        return !($post_type instanceof WP_Error);
    }

    /**
     * Registers a post type for saving users attributes when 'Database' is selected as storage type
     * @return bool
     * @since 0.1.0
     */
    public final function register_user_attributes_post_type(): bool
    {

        $storage_mode = Redux::get_option($this->plugin_slug . '_settings', 'storage-mode');
        if ($storage_mode !== 'database') return false;

        $admin_cap = "manage_options";
        $args = [
            'exclude_from_search' => true,
            'show_in_rest' => false,
            'query_var' => false,
            'rewrite' => false,
            'capabilities' => [
                'edit_post' => $admin_cap,
                'read_post' => $admin_cap,
                'delete_post' => $admin_cap,
                'edit_posts' => $admin_cap,
                'edit_others_posts' => $admin_cap,
                'publish_posts' => $admin_cap,
                'read_private_posts' => $admin_cap,
                'create_posts' => $admin_cap,
            ],
            'supports' => ['title', 'author'],
            'delete_with_user' => true
        ];

        $post_type = register_post_type($this->plugin_slug . '_ua', $args);
        return !($post_type instanceof WP_Error);
    }

    /**
     * Registers meta boxes for selectable attributes post type
     */
    public final function register_selectable_attributes_meta_box()
    {
        add_meta_box($this->plugin_slug . '-wc-attributes', esc_html__('Selectable Attributes', 'cupcode-mywc'), [$this, 'load_selectable_attributes_meta_box_content'], $this->plugin_slug . '_sa', 'normal', 'high');
    }

    /**
     * Loads contents for selectable attributes meta box
     * @param $post
     */
    public final function load_selectable_attributes_meta_box_content($post)
    {
        $html = '<select required name="' . $this->plugin_slug . '-attributes">';
        foreach (wc_get_attribute_taxonomies() as $attribute_taxonomy)
            $html .= '<option value="'. $attribute_taxonomy->attribute_id .'">' . $attribute_taxonomy->attribute_label .'</option>';
        $html .= '</select>';
        echo $html;
    }

}