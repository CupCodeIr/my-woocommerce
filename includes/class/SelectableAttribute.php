<?php


namespace CupCode\MyWooCommerce;

defined('ABSPATH') or die('No script kiddies please!');


use WP_Error;

class SelectableAttribute extends Attribute
{

    private static $instance;


    private function __construct()
    {
        parent::__construct();
    }


    /**
     * Registers a post type for saving attributes which are selectable for customers and those are for customer
     * @return bool
     * @since 0.1.0
     */
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

        add_action('init', function () {

            $this->register_attribute_post_type();
            add_action('save_post_' . CC_MYWC_PLUGIN_SLUG . '_sa', [$this, 'save_attributes']);
            add_filter('redirect_post_location', [$this, 'maybe_redirect_for_error_display']);
            add_filter('post_updated_messages', [$this, 'post_type_save_message']);
            add_filter('admin_notices', [$this, 'post_type_save_error_display']);

        });
    }

    /**
     * Registers a post type for saving attributes which are selectable for customers
     * @return bool
     * @since 0.1.0
     */

    public function register_attribute_post_type(): bool
    {

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
            'item_published' => esc_html_x('Selectable Attribute published.', 'post-type', 'cupcode-mywc'),
            'item_published_privately' => esc_html_x('Selectable Attribute published privately.', 'post-type', 'cupcode-mywc'),
            'item_reverted_to_draft' => esc_html_x('Selectable Attribute reverted to draft.', 'post-type', 'cupcode-mywc'),
            'item_scheduled' => esc_html_x('Selectable Attribute scheduled.', 'post-type', 'cupcode-mywc'),
            'item_updated' => esc_html_x('Selectable Attribute updated.', 'post-type', 'cupcode-mywc'),
        ];

        $args = [
            'labels' => $labels,
            'public' => true,
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => CC_MYWC_PLUGIN_SLUG . '_settings_options',
            'show_in_rest' => false,
            'query_var' => false,
            'rewrite' => false,
            'capabilities' => [
                'edit_post' => self::$admin_cap,
                'read_post' => self::$admin_cap,
                'delete_post' => self::$admin_cap,
                'edit_posts' => self::$admin_cap,
                'edit_others_posts' => self::$admin_cap,
                'publish_posts' => self::$admin_cap,
                'read_private_posts' => self::$admin_cap,
                'create_posts' => self::$admin_cap,
            ],
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => ['title', 'author'],
            'delete_with_user' => false,
            'register_meta_box_cb' => [$this, 'register_selectable_attributes_meta_box']
        ];

        $post_type = register_post_type(CC_MYWC_PLUGIN_SLUG . '_sa', $args);
        return !($post_type instanceof WP_Error);

    }

    /**
     * Registers meta boxes for selectable attributes post type
     * @since 0.1.0
     */
    public final function register_selectable_attributes_meta_box()
    {
        add_meta_box(CC_MYWC_PLUGIN_SLUG . '-wc-attributes', esc_html__('Properties', 'cupcode-mywc'), [$this, 'load_selectable_attributes_meta_box_content'], CC_MYWC_PLUGIN_SLUG . '_sa', 'normal', 'high');
    }

    /**
     * Loads contents for selectable attributes meta box
     * @param $post
     * @since 0.1.0
     */
    public final function load_selectable_attributes_meta_box_content($post)
    {
        $locale = $this->get_local_language_code();
        $attribute_taxonomies = wc_get_attribute_taxonomies();
        $wc_excluded_terms = $this->get_used_taxonomies();
        $wc_remained_taxonomies = get_terms([
            'taxonomy' => ['product_cat', 'product_tag'],
            'hide_empty' => true,
            'count' => false,
            'exclude' => $wc_excluded_terms
        ]);
        $wc_remained_categories = [];
        $wc_remained_tags = [];
        foreach ($wc_remained_taxonomies as $remained_taxonomy) {
            if ($remained_taxonomy->taxonomy === "product_cat")
                $wc_remained_categories[] = $remained_taxonomy;
            elseif ($remained_taxonomy->taxonomy === "product_tag")
                $wc_remained_tags[] = $remained_taxonomy;
        }
        $this->get_template('admin-attribute-edit-page-meta-box',
            [
                'locale' => $locale,
                'attribute_taxonomies' => $attribute_taxonomies,
                'selected_data' => $this->get_post_attribute_meta($post->ID),
                'categories' => $wc_remained_categories,
                'post_id' => $post->ID,
                'categories_map' => wp_list_pluck($wc_remained_categories, 'name', 'term_id'),
                'tags' => $wc_remained_tags
            ]
        );
    }

    /**
     * Changes post save or update message if there is an error.
     * @param array $messages
     * @return array
     * @since  0.1.0
     */
    public final function post_type_save_message(array $messages): array
    {


        $messages['post'][1] = esc_html__('Selectable Attribute updated.', 'cupcode-mywc');
        $messages['post'][4] = esc_html__('Selectable Attribute updated.', 'cupcode-mywc');
        $messages['post'][6] = esc_html__('Selectable Attribute published.', 'cupcode-mywc');
        $messages['post'][9] = esc_html__('Selectable Attribute updated.', 'cupcode-mywc');
        $messages['post'][10] = esc_html__('Selectable Attribute draft updated.', 'cupcode-mywc');

        return $messages;


    }

    /**
     * Adds a query string to redirect url after post save or update in order to show an error if needed.
     * @param $location
     * @return string
     * @since 0.1.0
     */
    public final function maybe_redirect_for_error_display($location): string
    {
        $error = isset($_POST[CC_MYWC_PLUGIN_SLUG . '_admin_attr_save_error']) ? $_POST[CC_MYWC_PLUGIN_SLUG . '_admin_attr_save_error'] : false;
        if ($error !== false)
            $location .= "&" . CC_MYWC_PLUGIN_SLUG . "_admin_attr_save_error={$error}";
        return $location;
    }

    /**
     *
     */
    public final function post_type_save_error_display()
    {

        if (isset($_GET[CC_MYWC_PLUGIN_SLUG . '_admin_attr_save_error'])) {

            $html = '<div class="notice notice-error is-dismissible">';
            $error_code = $_GET[CC_MYWC_PLUGIN_SLUG . '_admin_attr_save_error'];
            if ($error_code === "no_cat_tag_passed") {
                $html .= '<p><strong>' . esc_html__('Please select at least a product category or a product tag.', 'cupcode-mywc') . '</strong></p>';
            }
            $html .= '</div>';
            echo $html;
        }

    }

    /**
     * Saves selectable attributes data as post meta
     * @param int $post_id Post ID
     * @return bool
     * @since 0.1.0
     */
    public function save_attributes(int $post_id): bool
    {

        $nonce_name = isset($_POST[CC_MYWC_PLUGIN_SLUG . '_save_selectable_attributes_nonce']) ? $_POST[CC_MYWC_PLUGIN_SLUG . '_save_selectable_attributes_nonce'] : '';
        $nonce_action = CC_MYWC_PLUGIN_SLUG . '_save_selectable_attributes';

        // Check if nonce is valid.
        if (!wp_verify_nonce($nonce_name, $nonce_action)) {
            return false;
        }
        // Check if user has permissions to save data.
        if (!current_user_can(self::$admin_cap, $post_id)) {
            return false;
        }

        // Check if not an autosave.
        if (wp_is_post_autosave($post_id)) {
            return false;
        }

        // Check if not a revision.
        if (wp_is_post_revision($post_id)) {
            return false;
        }

        $categories = $_POST['cupcode_mywc-categories'];
        $tags = $_POST['cupcode_mywc-tags'];
        $attributes = $_POST['cupcode_mywc-attributes'];

        if ((count($categories) < 1 && count($tags) < 1) || count($attributes) < 1) {
            $_POST[CC_MYWC_PLUGIN_SLUG . '_admin_attr_save_error'] = 'no_cat_tag_passed';
            return false;
        }
        $insert_query = "insert into {$this->wpdb->postmeta} (post_id,meta_key,meta_value) values ";
        $insert_values = [];
        foreach ($categories as $category){
            $insert_values[] = $this->wpdb->prepare("(%d,%s,%s)",$post_id,'_' . CC_MYWC_PLUGIN_SLUG . '_category',$category);
        }
        foreach ($tags as $tag){
            $insert_values[] = $this->wpdb->prepare("(%d,%s,%s)",$post_id,'_' . CC_MYWC_PLUGIN_SLUG . '_tag',$tag);
        }
        foreach ($attributes as $attribute){
            $insert_values[] = $this->wpdb->prepare("(%d,%s,%s)",$post_id,'_' . CC_MYWC_PLUGIN_SLUG . '_attribute',$attribute);
        }

        $insert_values[] = $this->wpdb->prepare("(%d,%s,%s)",$post_id,'_' . CC_MYWC_PLUGIN_SLUG . '_hash',md5(implode(',',$attributes)));


        $insert_query .= implode(",\n",$insert_values);

        $delete_query = "delete from {$this->wpdb->postmeta} where post_id = {$post_id} and 
                        (meta_key = '_" . CC_MYWC_PLUGIN_SLUG . "_category' or
                        meta_key = '_" . CC_MYWC_PLUGIN_SLUG . "_tag' or
                        meta_key = '_" . CC_MYWC_PLUGIN_SLUG . "_attribute')";
        if($this->wpdb->query($delete_query) !== false)
            return ($this->wpdb->query($insert_query) === (count($categories) + count($tags) + count($attributes) ));
        return false;




    }
}