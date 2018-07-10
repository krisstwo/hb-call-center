jQuery(document).ready(function ($) {

    //trigger save items automaticly after alteration
    $(document).ajaxSuccess(function (event, xhr, settings) {
        // console.log(settings);
        if (settings.data && (settings.data.indexOf('action=woocommerce_add_order_item') !== -1 || settings.data.indexOf('action=woocommerce_remove_order_item') !== -1)) {
            $("button.save-action").trigger("click");
        }
    });

    //trigger a custom modal for adding order items
    var openAddItemModal = function () {
        $(this).WCBackboneModal({
            template: 'wc-modal-hb-add-products'
        });

        return false;
    };

    $('#woocommerce-order-items')
        .off('click', 'button.add-order-item')
        .on('click', 'button.add-order-item', function (e) {
            e.preventDefault();
            openAddItemModal();

            return false;
        });

    $(document.body)
        .on('wc_backbone_modal_response', function (e, target, data) {
            if (target === 'wc-modal-hb-add-products') {
                $(document.body).trigger('wc_backbone_modal_response', ['wc-modal-add-products', data]);
            }
        });
});