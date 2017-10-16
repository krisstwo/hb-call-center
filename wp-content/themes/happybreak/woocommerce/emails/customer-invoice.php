<?php


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>


    <p><?php _e( ' Bonjour ', 'happybreak' ); ?> <?php echo $order->get_billing_first_name() . ' ' .$order->get_billing_last_name()  ?></p>

    <p> <?php _e( 'Nous avons bien pris en compte votre précommande passée le', 'happybreak' ); ?>
        <?php
        $cls_date = new DateTime($order->get_date_created());
        echo $cls_date->format('d/m/Y -  h:i'); ?>
        <?php _e( 'et nous vous en remercions.', 'happybreak' ); ?>


    </p>
  <?php if ( $order->has_status( 'pending' ) ) : ?>
    <p><?php printf( __( 'Cliquez sur ce lien, et commandez en ligne: %2$s', 'happybreak' ), get_bloginfo( 'name', 'display' ), '<a href="' . esc_url( $order->get_checkout_payment_url() ) . '">' . __( 'payer', 'happybreak' ) . '</a>' ); ?></p>
<?php endif; ?>


    <p><?php _e( 'Nous vous remercions et vous souhaitons de très beaux séjours', 'happybreak' ); ?>
        </p>
<?php

/**
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
?>