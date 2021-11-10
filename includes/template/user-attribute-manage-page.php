<?php
/**
 * Template File
 * Content for customer attributes management pages
 */

defined('ABSPATH') or die('No script kiddies please!');
?>
<section id="mywc-customer-attributes-management">

    <div class="mywc-intro">
        <?php echo $intro_text ?>
    </div>
    <div class="mywc-add-attribute">
        <h3><?php _e('Add new attribute','cupcode-mywc'); ?></h3>
        <div class="mywc-add-attribute__termSelect">
            <p class="woocommerce-form-row form-row">
                <label for="mywc-add-attribute__termSelect__term"><?php
                    /* translator: label for select box in add attribute page */
                    _e('Category : ','cupcode-mywc');
                    ?></label>
                <select class="woocommerce-select-control woocommerce-select-control__control-input" autocomplete="off" name="mywc-add-attribute__termSelect__term" id="mywc-add-attribute__termSelect__term">
                <?php
                echo '<option>' . __('Choose a category','cupcode-mywc') . '</option>';
                foreach ($selectable_items as $key => $item){
                    if(isset($item['category']))
                        foreach ($item['category'] as $category) {
                            echo '<option data-group="'. $key .'" value="' . $category .'">' . get_term($category)->name .'</option>';
                        }
                    if(isset($item['tag']))
                        foreach ($item['tag'] as $tag) {
                            echo '<option data-group="'. $key .'" value="' . $tag .'">' . get_term($tag)->name .'</option>';
                        }
                }
                ?>
                </select>
            </p>
        </div>
        <div class="mywc-add-attribute__attribute">
            
        </div>
    </div>


</section>
