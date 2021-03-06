<?php


namespace CupCode\MyWooCommerce;

defined('ABSPATH') or die('No script kiddies please!');

class MyWooCommerce
{
    private static $instance;
    private $customerAttribute,$selectableAttribute;

    /**
     * @return MyWooCommerce
     * @since 0.1.0
     */
    public static function get_instance() : MyWooCommerce
    {
        if(self::$instance === null) {
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }

    /**
     *
     * @since 0.1.0
     */
    private function init()
    {
        $this->selectableAttribute = SelectableAttribute::get_instance();
        $this->customerAttribute = CustomerAttribute::get_instance();
        register_activation_hook(CC_MYWC_PLUGIN_BASENAME, function () {

            $this->copy_translations();
            $this->handle_plugin_version();
            flush_rewrite_rules();


        });
        register_deactivation_hook(CC_MYWC_PLUGIN_BASENAME,function (){
            flush_rewrite_rules();
        });
        add_action('admin_enqueue_scripts', [$this,'enqueue_admin_scripts']);
        add_action('wp_enqueue_scripts', [$this,'enqueue_public_scripts']);
    }

    /**
     * Updates plugin version in wp_options
     * @since 0.1.0
     */
    private function handle_plugin_version(){

        update_option(CC_MYWC_PLUGIN_SLUG . '_version',CC_MYWC_PLUGIN_VERSION);

    }

    /**
     * A function to copy plugin translations to Wordpress languages directory
     * @since 0.1.0
     */
    private function copy_translations(){

        $mo_files = glob(CC_MYWC_PLUGIN_PATH . '/languages/*.mo');
        foreach ($mo_files as $mo_file)
            copy($mo_file, trailingslashit(WP_CONTENT_DIR) . '/languages/plugins/' . wp_basename($mo_file));

    }

    /**
     * Enqueue styles and scripts for admin environment
     * @param $hook
     * @since 0.1.0
     */
    public function enqueue_admin_scripts($hook)
    {
        global $post_type;
        if(is_admin() && ($hook === 'post-new.php' || $hook === 'post.php') && $post_type ===  CC_MYWC_PLUGIN_SLUG . '_sa'){
            wp_enqueue_style(CC_MYWC_PLUGIN_SLUG . '-select2', CC_MYWC_PLUGIN_URL . 'assets/css/select2.min.css', [], false, false);
            wp_enqueue_script(CC_MYWC_PLUGIN_SLUG . '-select2', CC_MYWC_PLUGIN_URL . 'assets/js/select2/select2.min.js', [], false, true);
            wp_enqueue_script(CC_MYWC_PLUGIN_SLUG . '-select2-lang', CC_MYWC_PLUGIN_URL . 'assets/js/select2/i18n/' . Utils::get_local_language_code() . '.js', [], false, true);

        }

    }

    /**
     * Enqueue styles and scripts for non-admin environment
     * @since 0.1.0
     */
    public function enqueue_public_scripts()
    {
        if($this->is_attributes_management_endpoint()){

            wp_enqueue_script(CC_MYWC_PLUGIN_SLUG . '-customer-attributes', CC_MYWC_PLUGIN_URL . 'assets/js/customer-attributes-manage.js', ['jquery'], '0.1.0', true);
            wp_localize_script(CC_MYWC_PLUGIN_SLUG . '-customer-attributes','MyWooCommerce',[
                'attribute_set' => $this->selectableAttribute->get_formatted_selectable_attributes_by_taxonomy()
            ]);
            wp_enqueue_style(CC_MYWC_PLUGIN_SLUG . '-customer-attributes', CC_MYWC_PLUGIN_URL . 'assets/css/customer-attributes-manage.css', [], '0.1.0', false);
        }
    }

    /**
     * @param $code
     * @return string
     * @since 0.1.0
     */
    public function get_message_from_code($code): string
    {

        $notices = [
            0 => _x("Something went wrong, please try again.",'general-notice','cupcode-mywc'),
            1 => _x("You are not allowed to perform this action.",'general-notice','cupcode-mywc'),
            2 => _x("Attribute saved successfully!",'general-notice','cupcode-mywc'),
            3 => _x("Please fill and set all the fields!",'general-notice','cupcode-mywc')
        ];
        return isset($notices[$code]) ? $notices[$code] : '';

    }

    /**
     * Returns true if the current page which is vied by user is attribute management page
     * @return bool
     * @since 0.1.0
     */
    public function is_attributes_management_endpoint(): bool
    {
        global $wp;
        return is_account_page() && array_key_exists($this->customerAttribute->get_wc_add_attribute_endpoint_slug(),$wp->query_vars);

    }
}