<?php


class Attribute
{
    private $plugin_slug;

    public function __construct($plugin_slug)
    {
        $this->plugin_slug = $plugin_slug;
        add_action( 'init', [$this,'register_selectable_attribute_post_type'] );
    }

    private function register_selectable_attribute_post_type()
    {
        $admin_cap = "manage_options";
        $labels = [
            /* translator: Selectable Attributes post type name */
            'name'                  => esc_html_x( 'Selectable Attributes', 'post-type', 'cupcode-mywc' ),
            /* translator: Selectable Attributes post type singular name */
            'singular_name'         => esc_html_x( 'Selectable Attribute', 'post-type', 'cupcode-mywc' ),
            /* translator: Selectable Attributes post type menu name */
            'menu_name'             => esc_html_x( 'Selectable Attributes', 'post-type', 'cupcode-mywc' ),
            /* translator: Selectable Attributes post type admin bar name */
            'name_admin_bar'        => esc_html_x( 'Selectable Attribute', 'post-type', 'cupcode-mywc' ),
            'add_new'               => esc_html_x( 'Add New','post-type', 'cupcode-mywc' ),
            'add_new_item'          => esc_html_x( 'Add New Selectable Attribute','post-type', 'cupcode-mywc' ),
            'new_item'              => esc_html_x( 'New Selectable Attribute','post-type', 'cupcode-mywc' ),
            'edit_item'             => esc_html_x( 'Edit Selectable Attribute','post-type', 'cupcode-mywc' ),
            'view_item'             => esc_html_x( 'View Selectable Attribute','post-type', 'cupcode-mywc' ),
            'all_items'             => esc_html_x( 'All Selectable Attributes','post-type', 'cupcode-mywc' ),
            'search_items'          => esc_html_x( 'Search Selectable Attributes','post-type', 'cupcode-mywc' ),
            'not_found'             => esc_html_x( 'No selectable attributes found.','post-type', 'cupcode-mywc' ),
            'not_found_in_trash'    => esc_html_x( 'No Selectable Attributes found in Trash.','post-type', 'cupcode-mywc' ),
            'archives'              => esc_html_x( 'Selectable Attribute archives','post-type',  'cupcode-mywc' ),
            'filter_items_list'     => esc_html_x( 'Filter selectable attribute list','post-type',  'cupcode-mywc' ),
            'items_list_navigation' => esc_html_x( 'Selectable Attributes list navigation','post-type',   'cupcode-mywc' ),
            'items_list'            => esc_html_x( 'Selectable Attributes list', 'post-type', 'cupcode-mywc' ),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => 'admin.php?page=' . $this->plugin_slug . '_settings_options',
            'show_in_rest'          => false,
            'query_var'          => false,
            'rewrite'            => false,
            'capabilities' => [
                'edit_post'          => $admin_cap,
                'read_post'          => $admin_cap,
                'delete_post'        => $admin_cap,
                'edit_posts'         => $admin_cap,
                'edit_others_posts'  => $admin_cap,
                'publish_posts'      => $admin_cap,
                'read_private_posts' => $admin_cap,
                'create_posts'       => $admin_cap,
            ],
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => [ 'title', 'author','custom-fields' ],
            'taxonomies' => ['product_tag','product_cat'],
            'delete_with_user' => false
        ];

        register_post_type( $this->plugin_slug . '_sa', $args );
    }

}