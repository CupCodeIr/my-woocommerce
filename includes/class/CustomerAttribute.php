<?php


namespace CupCode\MyWooCommerce;

defined('ABSPATH') or die('No script kiddies please!');

use Redux;
use WP_Error;

class CustomerAttribute extends Attribute
{

    use Template;

    private static $instance;
    private $storage_mode,$attr_limit,$multiple_attr_allowed;

    protected function __construct()
    {
        parent::__construct();
    }

    public static function get_instance(): Attribute
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance->init();


        }
        return self::$instance;
    }

    protected function init()
    {
        parent::init();
        $this->storage_mode = Redux::get_option(CC_MYWC_PLUGIN_SLUG . '_settings','storage-mode','database');
        $this->attr_limit = Redux::get_option(CC_MYWC_PLUGIN_SLUG . '_settings','customer-attr-add-limit',5);
        $this->multiple_attr_allowed = Redux::get_option(CC_MYWC_PLUGIN_SLUG . '_settings','customer-attr-add-same-multiple');
        add_action('init', function () {

            $this->register_attribute_post_type();
        });
        $this->register_customer_attribute_panel();
    }

    /**
     * Registers a post type for saving customer attributes
     * @return bool
     * @since 0.1.0
     */

    public function register_attribute_post_type(): bool
    {

        if ($this->storage_mode !== 'database') return false;

        $admin_cap = "manage_options";
        $args = [
            'labels' => ['name' => esc_html_x('Customer Attributes', 'post-type', 'cupcode-mywc')],
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

    /**
     * Registers customer Attributes endpoint for WooCommerce
     * @since 0.1.0
     */
    public function register_customer_attribute_panel()
    {
        $endpoint = get_option(CC_MYWC_PLUGIN_SLUG . '_attribute_endpoint');
        $menu_name = Redux::get_option(CC_MYWC_PLUGIN_SLUG . '_settings', 'customer-attr-page-title');
        add_action('init', function () use ($endpoint) {
            add_rewrite_endpoint(sanitize_title($endpoint),  EP_PAGES);
        });
        add_filter('woocommerce_account_menu_items', function ($items) use ($menu_name, $endpoint) {
            $logout = $items['customer-logout'];
            unset($items['customer-logout']);
            $items[$endpoint] = $menu_name;
            $items['customer-logout'] = $logout;
            return $items;
        });
        add_action('woocommerce_account_' . rawurldecode($endpoint) . '_endpoint', function () {

            $saved_count = $this->get_customer_attributes_count(get_current_user_id());
            $intro_filtered_text = Redux::get_option(CC_MYWC_PLUGIN_SLUG . '_settings','customer-attr-page-intro','');
            $intro_filtered_text = str_replace('{remained_count}',$this->attr_limit - $saved_count,$intro_filtered_text);
            $this->get_template('user-attribute-manage-page',
                [
                    'intro_text' => $intro_filtered_text,
                    'remained_count' => $this->attr_limit - $saved_count
                ]
                ,true);
        });
    }

    /**
     * Counts customer saved attributes which may be saved inside database or using cookie.
     * @param $user_id
     * @return int
     * @since 0.1.0
     */
    private function get_customer_attributes_count($user_id): int
    {
        if($this->storage_mode === 'database'){
            if($user_id === 0) return 0;
            return wp_count_posts(CC_MYWC_PLUGIN_SLUG . '_ua')->publish;

        }elseif($this->storage_mode === 'cookie'){
            //TODO
            if(isset($_COOKIE['my_woocommerce_data'])){
                $cookie_data = json_decode($_COOKIE['my_woocommerce_data']);
            }
        }
    }


    /**
     *
     */
    private function remove_expired_customer_attributes(){
        //TODO Implement method
    }

    private function get_customer_attributes()
    {
        //TODO
    }
}