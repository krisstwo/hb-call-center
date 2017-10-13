<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


class Happybreak_Meta_Box_Order_Payment
{

    /**
     * Output the metabox.
     *
     * @param WP_Post $post
     */
    public static function output($post)
    {
        global $theorder;

        // This is used by some callbacks attached to hooks such as woocommerce_order_actions which rely on the global to determine if actions should be displayed for certain orders.
        if (!is_object($theorder)) {
            $theorder = wc_get_order($post->ID);
        }
        ?>
        <ul class="order_actions">

            <li class="wide">
                <a href="<?php echo $theorder->get_checkout_payment_url(); ?>" target="_blank"><?php _e('Payer en tant que client', 'happybreak'); ?></a>
            </li>

            <li class="wide">
                <a href="<?php echo admin_url( 'admin-ajax.php?order_id=' . $theorder->ID . '&action=happybreak_send_order_email&email=customer_invoice' ); ?>"><?php _e('Envoyer un mail pour le paiement', 'happybreak'); ?></a>
            </li>

        </ul>
        <?php
    }
}
