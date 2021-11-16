<?php
/**
 * Template File
 * Content for customer attributes management pages
 */

defined('ABSPATH') or die('No script kiddies please!');
?>
<section id="mywc-customer-attributes-management" <?php echo(is_rtl() ? 'class="mywc-rtl"' : '') ?>>

    <div class="mywc-intro">
        <?php echo $intro_text ?>
    </div>
    <div class="mywc-add-attribute woocommerce">
        <form method="post" action="">
            <?php wp_nonce_field(CC_MYWC_PLUGIN_SLUG . 'new_attribute')?>
            <h3><?php _e('Add new attribute', 'cupcode-mywc'); ?></h3>
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label>
                    <?php
                    /* translator: label for select box in add attribute page */
                    _e('Name', 'cupcode-mywc');
                    ?>
                <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="mywc-new-attribute-name" placeholder="<?php _e('e.g. My Set','cupcode-mywc') ?>">
                    <span><em><?php _e('By naming your set you can find it easily in products pages','cupcode-mywc'); ?></em></span>
                </label>
            </p>
            <div class="mywc-add-attribute__termSelect">
                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                    <label for="mywc-add-attribute__termSelect__term"><?php
                        /* translator: label for select box in add attribute page */
                        _e('Category', 'cupcode-mywc');
                        ?></label>
                    <select class="woocommerce-select-control woocommerce-select-control__control-input"
                            autocomplete="off" name="mywc-new-attribute-term" id="mywc-add-attribute__termSelect__term">
                        <?php
                        echo '<option>' . __('Choose a category', 'cupcode-mywc') . '</option>';
                        ?>
                    </select>
                </p>
            </div>
            <div id="mywc-add-attribute__attributes" class="mywc-add-attribute__attributes">

            </div>
            <button type="submit" class="woocommerce-Button button" name="mywc_save_attribute" value="save"><?php _e('Save','cupcode-mywc'); ?></button>
        </form>
    </div>


</section>
