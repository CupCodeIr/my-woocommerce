jQuery(document).ready(function (){
    jQuery(document).on('change','#mywc-add-attribute__termSelect__term',function (){
        let selectedValue = jQuery(this).val();
        let selectedGroup = jQuery('option:selected',this).data('group');

    });
});