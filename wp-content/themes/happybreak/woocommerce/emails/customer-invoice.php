<?php


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>


    <p><?php _e( ' Bonjour ', 'happybreak' ); ?> <?php echo $order->get_billing_first_name() . ' ' .$order->get_billing_last_name()  ?></p>

<?php if (!(int)$order->get_meta($email->id . '_sent', true)) : ?>
    <p> <?php _e('Nous avons bien pris en compte votre précommande passée le', 'happybreak'); ?>
        <?php
        $cls_date = new DateTime($order->get_date_created());
        echo $cls_date->format('d/m/Y'); ?>
        <?php _e('et nous vous en remercions.', 'happybreak'); ?>

        <?php
        //alternate order details template for invoice
        wc_get_template('emails/email-order-invoice-details.php', array('order' => $order, 'sent_to_admin' => $sent_to_admin, 'plain_text' => $plain_text, 'email' => $email))
        ?>

    </p>
    <p>&nbsp;</p>
    <?php if ($order->has_status('pending')) : ?>
        <p><?php printf(__('Cliquez sur ce lien; et commandez en ligne : %2$s', 'happybreak'), get_bloginfo('name', 'display'), '<a href="' . esc_url($order->get_checkout_payment_url()) . '">' . __('payer', 'happybreak') . '</a>'); ?></p>
    <?php endif; ?>
<?php else : ?>
    <p><?php _e( 'Sauf erreur de ma part je n’ai pas encore reçu votre paiement.', 'happybreak' ); ?></p>
    <p><?php _e( 'Si vous avez des questions, vous pouvez me contacter au 09 80 01 01 01 (appel non surtaxé).', 'happybreak' ); ?></p>
    <?php
    //alternate order details template for invoice
    wc_get_template('emails/email-order-invoice-details.php', array('order' => $order, 'sent_to_admin' => $sent_to_admin, 'plain_text' => $plain_text, 'email' => $email))
    ?>
    <p>&nbsp;</p>
    <?php if ($order->has_status('pending')) : ?>
        <p><?php printf(__('Sinon, cliquez sur ce lien; et commandez en ligne : %2$s', 'happybreak'), get_bloginfo('name', 'display'), '<a href="' . esc_url($order->get_checkout_payment_url()) . '">' . __('payer', 'happybreak') . '</a>'); ?></p>
    <?php endif; ?>
<?php endif; ?>

    <p><?php _e( 'Nous vous remercions et vous souhaitons de très beaux séjours', 'happybreak' ); ?>
        </p>
<?php

/**
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
?>