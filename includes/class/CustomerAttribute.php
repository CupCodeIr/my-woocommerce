<?php


namespace CupCode\MyWooCommerce;


use Redux;
use WP_Error;

class CustomerAttribute extends Attribute
{


    private static $instance;
    protected $wpdb;

    private function __construct(){
        
    }


    public static function get_instance(): Attribute
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance->init();
            global $wpdb;
            self::$instance->wpdb = $wpdb;
        }
        return self::$instance;
    }

    protected function init()
    {
        add_action('init', function () {

            $this->register_attribute_post_type();
            add_action('save_post_'. CC_MYWC_PLUGIN_SLUG . '_sa', [$this, 'save_attributes']);

        });
    }

    /**
     * Registers a post type for saving customer attributes
     * @return bool
     * @since 0.1.0
     */

    public function register_attribute_post_type() : bool
    {

        $storage_mode = Redux::get_option(CC_MYWC_PLUGIN_SLUG . '_settings', 'storage-mode');
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

        $post_type = register_post_type(CC_MYWC_PLUGIN_SLUG . '_ua', $args);
        return !($post_type instanceof WP_Error);

    }

    public function save_attributes(int $post_id): bool
    {
        // TODO: Implement save_attributes() method.
        return true;
    }
}