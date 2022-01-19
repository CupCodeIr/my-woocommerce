<?php


namespace CupCode\MyWooCommerce;

defined('ABSPATH') or die('No script kiddies please!');

use Redux;
use WP_Error;

class CustomerAttribute extends Attribute
{

    use Template;

    private static $instance;
    private $storage_mode, $attribute_limit, $guest_mode, $multiple_attr_allowed, $add_attribute_wc_endpoint_slug, $plugin, $db_record;

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
        $this->storage_mode = Redux::get_option(CC_MYWC_PLUGIN_SLUG . '_settings', 'storage-mode', 'database');
        $this->attribute_limit = Redux::get_option(CC_MYWC_PLUGIN_SLUG . '_settings', 'customer-attr-add-limit', 5);
        $this->multiple_attr_allowed = Redux::get_option(CC_MYWC_PLUGIN_SLUG . '_settings', 'customer-attr-add-same-multiple');
        $this->add_attribute_wc_endpoint_slug = get_option(CC_MYWC_PLUGIN_SLUG . '_attribute_endpoint');
        $this->guest_mode = Redux::get_option(CC_MYWC_PLUGIN_SLUG . '_settings', 'guest-use');
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

    /**
     * Registers customer Attributes endpoint for WooCommerce
     * @since 0.1.0
     */
    public function register_customer_attribute_panel()
    {
        $endpoint = $this->get_wc_add_attribute_endpoint_slug();
        $menu_name = Redux::get_option(CC_MYWC_PLUGIN_SLUG . '_settings', 'customer-attr-page-title');
        add_action('init', function () use ($endpoint) {
            add_rewrite_endpoint(sanitize_title($endpoint), EP_PAGES);
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
            $current_user_id = get_current_user_id();
            if (isset($_POST['mywc-save-attribute'])) {

                $wp_nonce = isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : '';

                if (!wp_verify_nonce($wp_nonce, CC_MYWC_PLUGIN_SLUG . 'new_attribute')) $message['error'][] = $this->plugin->get_message_from_code(0);
                else {

                    $term_id = isset($_POST['mywc-new-attribute-term']) ? intval($_POST['mywc-new-attribute-term']) : 0;
                    $attribute_name = isset($_POST['mywc-new-attribute-name']) ? sanitize_text_field($_POST['mywc-new-attribute-name']) : '';
                    $attribute_values = isset($_POST['mywc-new-attribute-value']) ? $_POST['mywc-new-attribute-value'] : [];
                    $attribute_set = [];
                    foreach ($attribute_values as $key => $attribute) {
                        $attribute_set[] = [
                            'id' => intval($key),
                            'value' => intval($attribute),
                        ];
                    }
                    if (mb_strlen($attribute_name) < 1 || count($attribute_set) < 1)
                        $message['error'][] = $this->plugin->get_message_from_code(3);
                    else if ($this->can_customer_save_attribute($current_user_id, $term_id, $attribute_set)) {
                        $taxonomy = $this->get_taxonomy_name_from_term($term_id);
                        $this->set_customer_attribute($current_user_id,$attribute_name,$term_id,$taxonomy,$attribute_set);
                        $this->save_attribute();
                        $message['notice'][] = $this->plugin->get_message_from_code(2);
                    } else {
                        $message['error'][] = $this->plugin->get_message_from_code(1);
                    }
                }
            }
            $saved_count = $this->get_customer_attributes_count($current_user_id);
            $intro_filtered_text = Redux::get_option(CC_MYWC_PLUGIN_SLUG . '_settings', 'customer-attr-page-intro', '');
            $intro_filtered_text = str_ireplace('{remained_count}', $this->attribute_limit - $saved_count, $intro_filtered_text);
            $this->get_template('user-attribute-manage-page',
                [
                    'intro_text' => $intro_filtered_text,
                    'remained_count' => $this->attribute_limit - $saved_count,
                    'message' => $message
                ]
                , true);
        });
    }

    /**
     * Retrieves add attribute endpoint defined in settings
     * @return string
     * @since 0.1.0
     */
    public function get_wc_add_attribute_endpoint_slug(): string
    {

        return $this->add_attribute_wc_endpoint_slug;

    }

    /**
     * Checks if user can save attribute with given $attribute_id
     * - if current customer legitimate
     * - if term exists
     * - if user attributes haven't passed the limit
     * - if term can be selected
     * - if customer can save multiple attributes for same term
     * - if attributes and their values exist.
     * @param int $user_id
     * @param string $term_id
     * @param array $attributes
     * @return bool Whether data are valid to be saved.
     * @since 0.1.0
     */
    private function can_customer_save_attribute(int $user_id, string $term_id, array $attributes): bool
    {

        $current_user_id = $user_id;
        if ($current_user_id === 0 && !($this->guest_mode)) return false;

        $attribute_valid = $this->validate_customer_attribute($attributes, $term_id);
        if (!$attribute_valid) return false;

        $current_attributes_count = $this->get_customer_attributes_count($current_user_id);
        if ($current_attributes_count >= $this->attribute_limit) return false;

        $term_exists_in_user_attributes = $this->user_has_term($current_user_id, $term_id);
        if (($term_exists_in_user_attributes && !$this->multiple_attr_allowed)) return false;

        return true;


    }

    /**
     * Checks whether attributes are related to a term
     * @param array $attribute_value
     * @param int $term_id
     * @return bool
     */
    protected function validate_customer_attribute(array $attribute_value, int $term_id): bool
    {
        $selectable_attribute_instance = SelectableAttribute::get_instance();
        $selectable_data = $selectable_attribute_instance->get_formatted_selectable_attributes_by_taxonomy();

        foreach ($selectable_data as $datum) {

            $tags = isset($datum['tag']) ? wp_list_pluck($datum['tag'], 'id') : [];
            $categories = isset($datum['category']) ? wp_list_pluck($datum['category'], 'id') : [];
            $terms = array_merge($tags, $categories);
            if (in_array($term_id, $terms) && (count($datum['attribute']) === count($attribute_value))) {
                $av_ordered_list = wp_list_pluck($attribute_value, 'value', 'id');
                $attributes_ordered_list = wp_list_pluck($datum['attribute'], 'term', 'id');
                foreach ($av_ordered_list as $key => $item) {
                    if (isset($attributes_ordered_list[$key])) {
                        $value_ids = wp_list_pluck($attributes_ordered_list[$key], 'id');
                        if (!in_array($item, $value_ids)) return false;
                    } else return false;
                }
                return true;
            }
        }

        return false;
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
        if ($this->storage_mode === 'database') {
            if ($user_id === 0) return 0;
            $count = wp_count_posts(CC_MYWC_PLUGIN_SLUG . '_ua')->publish;

        } elseif ($this->storage_mode === 'cookie') {
            //TODO if using cookies, get data from cookie
            if (isset($_COOKIE[CC_MYWC_PLUGIN_SLUG . '_data'])) {
                $cookie_data = json_decode($_COOKIE[CC_MYWC_PLUGIN_SLUG . '_data'], true);
                $count = count($cookie_data['attribute_sets']);
            }
        }
        return $count;
    }

    /**
     * Checks whether the customer has profiled a term already
     * @param int $user_id
     * @param int $term_id
     * @return bool
     */
    protected function user_has_term(int $user_id, int $term_id): bool
    {

        return $this->wpdb->get_var($this->wpdb->prepare("SELECT COUNT(*) FROM {$this->wpdb->postmeta} INNER JOIN {$this->wpdb->posts} ON {$this->wpdb->postmeta}.post_id = {$this->wpdb->posts}.ID  where (meta_key = '_" . CC_MYWC_PLUGIN_SLUG . "_tag' OR meta_key = '_" . CC_MYWC_PLUGIN_SLUG . "_category') AND {$this->wpdb->posts}.post_type = '" . CC_MYWC_PLUGIN_SLUG . "_ua' AND {$this->wpdb->posts}.post_author = %s AND meta_value = %s", ["$user_id", $term_id]));
    }

    /**
     * Saves prepared record into database or other kinds of storage
     * @return bool
     */
    protected function save_attribute(): bool
    {
        if (!isset($this->db_record)) return false;

        if ($this->storage_mode === 'database') {

            $post = wp_insert_post([
                'post_title' => $this->db_record['title'],
                'post_content' => '',
                'post_author' => $this->db_record['author'],
                'post_status' => 'publish',
                'post_type' => CC_MYWC_PLUGIN_SLUG . '_ua',
                'meta_input' => $this->db_record['meta_data'],
            ]);
            return ($post !== 0);

        } elseif ($this->storage_mode === 'cookie') {

            $cookie_data = [];
            if (isset($_COOKIE[CC_MYWC_PLUGIN_SLUG . '_data'])) {

                $cookie_data = json_decode($_COOKIE[CC_MYWC_PLUGIN_SLUG . '_data'], true);
            }
            $cookie_data['attribute_sets'][] = [
                'title' => $this->db_record['title'],
                'attribute' => $this->db_record['meta_data'],
            ];
            setcookie(CC_MYWC_PLUGIN_SLUG . '_data', json_encode($cookie_data), time() + 31556926);
        }

    }

    private function remove_expired_customer_attributes()
    {
        //TODO Implement method
    }

    private function get_customer_attributes()
    {
        //TODO for both db and cookie
    }

    /**
     * Fills customer attribute variable record to be inserted into database or other kinds of storage
     * @param $customer_id
     * @param $title
     * @param $term_id
     * @param $term_type
     * @param $attributes
     * @since 0.10
     */
    private function set_customer_attribute($customer_id, $title, $term_id, $term_type, $attributes)
    {
        $this->db_record['title'] = $title;
        $this->db_record['author'] = $customer_id;
        $this->db_record['meta_data']['_' . CC_MYWC_PLUGIN_SLUG . '_' . $term_type] = $term_id;

        $formatted_attributes = [];
        foreach ($attributes as $attribute) {
            $formatted_attributes[] = [
                'attribute_id' => $attribute['id'],
                'attribute_value' => $attribute['value']
            ];
        }
        $this->db_record['meta_data']['_' . CC_MYWC_PLUGIN_SLUG . '_attribute_values'] = $formatted_attributes;

    }

    /**
     * Searches for taxonomy name of a term in selectable attributes
     * @param int $term_id
     * @return string
     * @since 0.1.0
     */
    protected function get_taxonomy_name_from_term(int $term_id): string{

        $selectable_attribute_instance = SelectableAttribute::get_instance();
        $selectable_data = $selectable_attribute_instance->get_formatted_selectable_attributes_by_taxonomy();

        foreach ($selectable_data as $selectable_datum){

            if(isset($selectable_datum['tag'])){
                foreach ($selectable_datum['tag'] as $item){
                    if($item['id'] === $term_id){
                        return 'tag';
                    }
                }
            }
            if(isset($selectable_datum['category'])){
                foreach ($selectable_datum['category'] as $item){
                    if($item['id'] === $term_id){
                        return 'category';
                    }
                }
            }
        }

    }

}