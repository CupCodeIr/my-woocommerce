<?php

/**
 * Template File
 * Content for admin attributes edit page meta box
 */

defined('ABSPATH') or die('No script kiddies please!');

?>

    <p><strong><?php esc_html_e('Selectable Product Categories', 'cupcode-mywc'); ?></strong></p>
    <p><?php esc_html_e('Please select the WooCommerce Product Categories that your customers can choose to define their desired Attributes in.', 'cupcode-mywc') ?></p>
    <select autocomplete="off" autocorrect="off" style="width:100%" multiple="multiple"
            dir="<?php echo(is_rtl() ? 'rtl' : 'ltr') ?>" lang="<?php echo $locale ?>"
            name="<?php echo CC_MYWC_PLUGIN_SLUG ?>-categories[]">';
        <?php foreach ($categories as $category) { ?>
            <option <?php echo (in_array($category->term_id,$selected_data['categories'])) ? 'selected="selected"' : '' ?> value="<?php echo $category->term_id ?>"><?php
                echo $category->name . ' ' . (($category->parent !== 0) ? sprintf(esc_html__('(Parent Category: %s)', 'cupcode-mywc'), $categories_map[$category->parent]) : '')
                ?></option>
        <?php } ?>
    </select>
    <small><?php esc_html_e('Empty categories are excluded.', 'cupcode-mywc'); ?></small>
    <hr>

    <p><strong><?php esc_html_e('Selectable Product Tags', 'cupcode-mywc'); ?></strong></p>
    <p><?php esc_html_e('Please select the WooCommerce Product Tags that your customers can choose to define their desired Attributes in.', 'cupcode-mywc') ?></p>
    <select autocomplete="off" autocorrect="off" style="width:100%" multiple="multiple"
            dir="<?php echo(is_rtl() ? 'rtl' : 'ltr') ?>" lang="<?php echo $locale ?>"
            name="<?php echo CC_MYWC_PLUGIN_SLUG ?>-tags[]">';
        <?php foreach ($tags as $tag) { ?>
            <option <?php echo (in_array($tag->term_id,$selected_data['tags'])) ? 'selected="selected"' : '' ?> value="<?php echo $tag->term_id ?>"><?php echo $tag->name ?></option>
        <?php } ?>
    </select>
    <small><?php esc_html_e('Empty tags are excluded.', 'cupcode-mywc'); ?></small>
    <hr>
    <p><strong><?php esc_html_e('Selectable Attributes', 'cupcode-mywc'); ?></strong></p>
    <p> <?php esc_html_e('Please select the WooCommerce Attributes that your customers can use to define their desired Attributes in selected product categories or tags.', 'cupcode-mywc') ?></p>
    <select autocomplete="off" autocorrect="off" style="width:100%" multiple="multiple"
            dir="<?php echo(is_rtl() ? 'rtl' : 'ltr') ?>" lang="<?php echo $locale ?>" required
            name="<?php echo CC_MYWC_PLUGIN_SLUG ?>-attributes[]">';
        <?php foreach ($attribute_taxonomies as $attribute_taxonomy) { ?>
            <option <?php echo (in_array($attribute_taxonomy->attribute_id,$selected_data['attributes'])) ? 'selected="selected"' : '' ?> value="<?php echo $attribute_taxonomy->attribute_id ?>"><?php echo $attribute_taxonomy->attribute_label ?></option>
        <?php } ?>
    </select>


    <script>
        jQuery(document).ready(function () {
            jQuery('select[name="<?php echo CC_MYWC_PLUGIN_SLUG ?>-attributes[]"]').select2({
                placeholder: "<?php esc_html_e('Click to select WooCommerce Attributes', 'cupcode-mywc') ?>"
            });
            jQuery('select[name="<?php echo CC_MYWC_PLUGIN_SLUG ?>-categories[]"]').select2({
                placeholder: "<?php esc_html_e('Click to select WooCommerce Product Categories', 'cupcode-mywc') ?>"
            });
            jQuery('select[name="<?php echo CC_MYWC_PLUGIN_SLUG ?>-tags[]"]').select2({
                placeholder: "<?php esc_html_e('Click to select WooCommerce Product Tags', 'cupcode-mywc') ?>"
            });
        });
    </script>
<?php wp_nonce_field(CC_MYWC_PLUGIN_SLUG . "_save_selectable_attributes", CC_MYWC_PLUGIN_SLUG . "_save_selectable_attributes_nonce");