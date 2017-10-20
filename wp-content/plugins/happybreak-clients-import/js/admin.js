jQuery(document).ready(function ($) {
    $(document).ajaxSuccess(function (event, xhr, settings) {
        // console.log(settings);
        if (settings.data && (settings.data.indexOf('action=woocommerce_add_order_item') !== -1 || settings.data.indexOf('action=woocommerce_remove_order_item') !== -1)) {
            $("button.save-action").trigger("click");
        }
    });
});