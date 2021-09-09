let select2;
jQuery(document).ready(function () {
    select2 = jQuery('select[name="' + select2_vars.plugin_slug +'-attributes"]').select2({
        multiple: true,
        language: select2_vars.language,
        rtl: select2_vars.is_rtl === "true"
    });
});