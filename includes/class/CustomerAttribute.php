<?php


namespace CupCode\MyWooCommerce;

defined('ABSPATH') or die('No script kiddies please!');

use Redux;
use WP_Error;

class CustomerAttribute extends Attribute
{

    use Template;

    private static $instance;
    private $storage_mode,$attribute_limit,$guest_mode,$multiple_attr_allowed,$add_attribute_wc_endpoint_slug,$plugin;

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
        $this->plugin = MyWooCommerce::get_instance();
        $this->storage_mode = Redux::get_option(CC_MYWC_PLUGIN_SLUG . '_settings','storage-mode','database');
        $this->attribute_limit = Redux::get_option(CC_MYWC_PLUGIN_SLUG . '_settings','customer-attr-add-limit',5);
        $this->multiple_attr_allowed = Redux::get_option(CC_MYWC_PLUGIN_SLUG . '_settings','customer-attr-add-same-multiple');
        $this->add_attribute_wc_endpoint_slug = get_option(CC_MYWC_PLUGIN_SLUG . '_attribute_endpoint');
        $this->guest_mode = Redux::get_option(CC_MYWC_PLUGIN_SLUG . '_settings','guest-use');
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
        $endpoint = $this->get_wc_add_attribute_endpoint_slug();
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

            $message = [];

            if(isset($_POST['mywc-save-attribute'])){

                if(!wp_verify_nonce($_POST['_wpnonce'],CC_MYWC_PLUGIN_SLUG . 'new_attribute')) $message['error'][] = $this->plugin->get_message_from_code(0);
                else{
                    if($this->can_customer_save_attribute($_POST['mywc-new-attribute-term'])){
                        $message['notice'][] = $this->plugin->get_message_from_code(2);
                    }else{
                        $message['error'][] = $this->plugin->get_message_from_code(1);
                    }
                }
            }
            $saved_count = $this->get_customer_attributes_count(get_current_user_id());
            $intro_filtered_text = Redux::get_option(CC_MYWC_PLUGIN_SLUG . '_settings','customer-attr-page-intro','');
            $intro_filtered_text = str_ireplace('{remained_count}',$this->attribute_limit - $saved_count,$intro_filtered_text);
            $this->get_template('user-attribute-manage-page',
                [
                    'intro_text' => $intro_filtered_text,
                    'remained_count' => $this->attribute_limit - $saved_count,
                    'message' => $message
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
        $count = 0;
        if($this->storage_mode === 'database'){
            if($user_id === 0) return 0;
            $count = wp_count_posts(CC_MYWC_PLUGIN_SLUG . '_ua')->publish;

        }elseif($this->storage_mode === 'cookie'){
            //TODO if using cookies, get data from cookie
            if(isset($_COOKIE['my_woocommerce_data'])){
                $cookie_data = json_decode($_COOKIE['my_woocommerce_data']);
            }
        }
        return $count;
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

    /**
     * Retrieves add attribute endpoint defined in settings
     * @return string
     * @since 0.1.0
     */
    public function get_wc_add_attribute_endpoint_slug() : string{

        return $this->add_attribute_wc_endpoint_slug;

    }

    private function can_customer_save_attribute($attribute_id): bool
    {
        $current_user_id = get_current_user_id();
        if($current_user_id === 0 && !($this->guest_mode)) return false;

        $current_attributes_count = $this->get_customer_attributes_count($current_user_id);
        if($current_attributes_count >= $this->attribute_limit) return false;

        global $wpdb;
        $attribute_exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->postmeta} where (meta_key = '_" . CC_MYWC_PLUGIN_SLUG ."_tag' OR meta_key = '_" . CC_MYWC_PLUGIN_SLUG ."_category') AND meta_value = %s",$attribute_id));
        if($attribute_exists && !$this->multiple_attr_allowed) return false;

        return true;


    }

    //TODO check for saved attributes and do not let customer to add again if its not allowed.
}