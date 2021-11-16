jQuery(document).ready(function () {
    let attributes = MyWooCommerce.attribute_set;
    let selectDOM = jQuery("#mywc-add-attribute__termSelect__term");
    let attributesDOM = jQuery("#mywc-add-attribute__attributes");
    jQuery.each(attributes, function (rootIndex, element) {
        if (element.hasOwnProperty('category')) {
            jQuery.each(element.category, function (index, element) {
                my_woocommerce_generate_category_option_dom(selectDOM,rootIndex,element.id,element.title);
            });
        }
        if (element.hasOwnProperty('tag')) {
            jQuery.each(element.tag, function (index, element) {
                my_woocommerce_generate_category_option_dom(selectDOM,rootIndex,element.id,element.title);
            });
        }
    });
    selectDOM.on('change', function () {
        let selectedValue = jQuery(this).val();
        let selectedGroup = jQuery('option:selected', this).data('group');
        if(typeof selectedGroup === 'undefined' || typeof selectedValue === 'undefined' ) return;
        attributesDOM.empty();
        jQuery.each(attributes[selectedGroup].attribute,function (index,element) {

            my_woocommerce_generate_attribute_input_dom(attributesDOM,element);

        })

    });
});

function my_woocommerce_generate_category_option_dom(DOM, rootIndex, element_id, element_title) {

    DOM.append('<option data-group="' + rootIndex + '" value="' + element_id + '">' + element_title + '</option>');
}

function my_woocommerce_generate_attribute_input_dom(DOM,attribute){

    let html = '<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">' +
        '<label>' +
        attribute.title +
        '<br>' +
        '<select name="mywc-new-attribute-value[' + attribute.id + ']" class="woocommerce-select-control woocommerce-select-control__control-input" autocomplete="off">';
    jQuery.each(attribute.term,function (index,element) {
        html += '<option value="' + element.id + '">' + element.title + '</option>';
    });
    html += '</select></label></p>'
    DOM.append(html);
}